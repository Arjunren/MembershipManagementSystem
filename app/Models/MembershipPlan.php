<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'description', 'duration_months', 'price', 'active'])]
class MembershipPlan extends Model
{
    protected function casts(): array
    {
        return ['price' => 'decimal:2', 'active' => 'boolean'];
    }
}
