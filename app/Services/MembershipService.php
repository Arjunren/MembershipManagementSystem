<?php

namespace App\Services;

use App\Models\CheckIn;
use App\Models\Membership;
use App\Models\MembershipPlan;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MembershipService
{
    public function enroll(User $member, MembershipPlan $plan, User $actor, array $payment): Membership
    {
        return DB::transaction(function () use ($member, $plan, $actor, $payment) {
            $lockedMember = User::lockForUpdate()->findOrFail($member->id);
            $lockedPlan = MembershipPlan::lockForUpdate()->findOrFail($plan->id);
            abort_unless($lockedMember->active && $lockedMember->role === 'member', 422, 'An active member account is required.');
            abort_unless($lockedPlan->active, 409, 'Selected membership plan is inactive.');
            abort_unless((int) round((float) $payment['amount'] * 100) === (int) round((float) $lockedPlan->price * 100), 422, 'Payment must match the plan price.');
            $latest = Membership::where('member_id', $lockedMember->id)->where('status', '!=', 'cancelled')->lockForUpdate()->orderByDesc('ends_on')->first();
            $startsOn = $latest && $latest->ends_on->isFuture() ? $latest->ends_on->copy()->addDay() : today();
            $membership = Membership::create(['member_id' => $lockedMember->id, 'membership_plan_id' => $lockedPlan->id, 'starts_on' => $startsOn, 'ends_on' => $startsOn->copy()->addMonthsNoOverflow($lockedPlan->duration_months)->subDay(), 'price' => $lockedPlan->price, 'status' => $startsOn->isFuture() ? 'scheduled' : 'active', 'created_by' => $actor->id]);
            $membership->payments()->create(['reference' => $payment['reference'] ?? 'PAY-'.strtoupper(Str::random(12)), 'amount' => $payment['amount'], 'method' => $payment['method'], 'received_by' => $actor->id, 'paid_at' => now()]);

            return $membership->load(['member', 'plan', 'payments']);
        });
    }

    public function checkIn(User $member, User $actor): CheckIn
    {
        return DB::transaction(function () use ($member, $actor) {
            User::lockForUpdate()->findOrFail($member->id);
            Membership::where('member_id', $member->id)->where('status', 'active')->where('ends_on', '<', today())->update(['status' => 'expired']);
            Membership::where('member_id', $member->id)->where('status', 'scheduled')->where('starts_on', '<=', today())->where('ends_on', '>=', today())->update(['status' => 'active']);
            $membership = Membership::where('member_id', $member->id)->where('status', 'active')->whereDate('starts_on', '<=', today())->whereDate('ends_on', '>=', today())->lockForUpdate()->first();
            abort_unless($membership, 409, 'Member does not have an active membership.');
            abort_if(CheckIn::where('member_id', $member->id)->where('check_in_date', today())->exists(), 409, 'Member has already checked in today.');

            return CheckIn::create(['member_id' => $member->id, 'membership_id' => $membership->id, 'checked_in_by' => $actor->id, 'check_in_date' => today(), 'checked_in_at' => now()]);
        });
    }
}
