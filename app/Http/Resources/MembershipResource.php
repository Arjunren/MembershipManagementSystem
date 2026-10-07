<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MembershipResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return ['id' => $this->id, 'status' => $this->status, 'starts_on' => $this->starts_on, 'ends_on' => $this->ends_on, 'price' => $this->price, 'member' => new UserResource($this->whenLoaded('member')), 'plan' => $this->whenLoaded('plan'), 'payments' => $this->whenLoaded('payments'), 'created_at' => $this->created_at];
    }
}
