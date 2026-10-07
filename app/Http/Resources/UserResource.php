<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return ['id' => $this->id, 'member_number' => $this->member_number, 'name' => $this->name, 'email' => $this->email, 'phone' => $this->phone, 'role' => $this->role, 'active' => $this->active, 'joined_on' => $this->joined_on];
    }
}
