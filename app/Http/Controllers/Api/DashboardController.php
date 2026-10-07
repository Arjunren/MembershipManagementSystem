<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CheckIn;
use App\Models\Membership;
use App\Models\MembershipPayment;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function __invoke(): JsonResponse
    {
        return response()->json(['data' => ['members' => User::where('role', 'member')->where('active', true)->count(), 'active_memberships' => Membership::where('status', 'active')->whereDate('ends_on', '>=', today())->count(), 'today_check_ins' => CheckIn::where('check_in_date', today())->count(), 'month_revenue' => MembershipPayment::whereBetween('paid_at', [now()->startOfMonth(), now()->endOfMonth()])->sum('amount')]]);
    }
}
