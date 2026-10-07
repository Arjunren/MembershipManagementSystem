<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CheckIn;
use App\Models\User;
use App\Services\MembershipService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CheckInController extends Controller
{
    public function __construct(private readonly MembershipService $memberships) {}

    public function store(Request $request, User $member): JsonResponse
    {
        return response()->json(['data' => $this->memberships->checkIn($member, $request->user())], 201);
    }

    public function index(Request $request): JsonResponse
    {
        return response()->json(CheckIn::when($request->filled('member_id'), fn ($query) => $query->where('member_id', $request->integer('member_id')))->latest('checked_in_at')->paginate(min($request->integer('per_page', 15), 100)));
    }
}
