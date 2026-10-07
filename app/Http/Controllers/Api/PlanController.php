<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MembershipPlan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PlanController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return response()->json(MembershipPlan::when(! $request->user()->isStaff(), fn ($query) => $query->where('active', true))->orderBy('price')->paginate(min($request->integer('per_page', 15), 100)));
    }

    public function store(Request $request): JsonResponse
    {
        return response()->json(['data' => MembershipPlan::create($this->validated($request))], 201);
    }

    public function update(Request $request, MembershipPlan $plan): JsonResponse
    {
        $plan->update($this->validated($request, $plan));

        return response()->json(['data' => $plan]);
    }

    private function validated(Request $request, ?MembershipPlan $plan = null): array
    {
        return $request->validate(['name' => ['required', 'string', 'max:120', Rule::unique('membership_plans')->ignore($plan)], 'description' => ['nullable', 'string', 'max:1000'], 'duration_months' => ['required', 'integer', 'min:1', 'max:120'], 'price' => ['required', 'numeric', 'min:0'], 'active' => ['sometimes', 'boolean']]);
    }
}
