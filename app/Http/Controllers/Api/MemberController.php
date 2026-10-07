<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MemberController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $members = User::where('role', 'member')->when($request->filled('search'), function ($query) use ($request) {
            $search = '%'.$request->string('search').'%';
            $query->where(fn ($nested) => $nested->where('name', 'like', $search)->orWhere('email', 'like', $search)->orWhere('member_number', 'like', $search));
        })->orderBy('name')->paginate(min($request->integer('per_page', 15), 100));

        return response()->json(UserResource::collection($members)->response()->getData(true));
    }

    public function show(User $member): UserResource
    {
        abort_unless($member->role === 'member', 404);

        return new UserResource($member);
    }

    public function update(Request $request, User $member): UserResource
    {
        abort_unless($member->role === 'member', 404);
        $member->update($request->validate(['name' => ['sometimes', 'string', 'max:120'], 'phone' => ['nullable', 'string', 'max:30'], 'active' => ['sometimes', 'boolean']]));

        return new UserResource($member);
    }
}
