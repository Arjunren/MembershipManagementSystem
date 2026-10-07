<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\MembershipResource;
use App\Models\Membership;
use App\Models\MembershipPlan;
use App\Models\User;
use App\Services\MembershipService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class MembershipController extends Controller
{
    public function __construct(private readonly MembershipService $memberships) {}

    public function index(Request $request): JsonResponse
    {
        $query = Membership::with(['member', 'plan', 'payments'])->when(! $request->user()->isStaff(), fn ($builder) => $builder->where('member_id', $request->user()->id))->when($request->filled('status'), fn ($builder) => $builder->where('status', $request->string('status')))->latest();

        return response()->json(MembershipResource::collection($query->paginate(min($request->integer('per_page', 15), 100)))->response()->getData(true));
    }

    public function show(Membership $membership): MembershipResource
    {
        Gate::authorize('view', $membership);

        return new MembershipResource($membership->load(['member', 'plan', 'payments']));
    }

    public function enroll(Request $request, User $member): JsonResponse
    {
        $data = $request->validate(['membership_plan_id' => ['required', 'exists:membership_plans,id'], 'amount' => ['required', 'numeric', 'min:0'], 'method' => ['required', 'in:cash,card,e_wallet'], 'reference' => ['nullable', 'string', 'max:100', 'unique:membership_payments,reference']]);
        $plan = MembershipPlan::findOrFail($data['membership_plan_id']);

        return response()->json(['data' => new MembershipResource($this->memberships->enroll($member, $plan, $request->user(), $data))], 201);
    }

    public function cancel(Membership $membership): MembershipResource
    {
        abort_if(in_array($membership->status, ['expired', 'cancelled'], true), 409, 'Membership is already inactive.');
        $membership->update(['status' => 'cancelled']);

        return new MembershipResource($membership->load(['member', 'plan', 'payments']));
    }
}
