<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['member_id', 'membership_id', 'checked_in_by', 'check_in_date', 'checked_in_at'])]
class CheckIn extends Model
{
    protected function casts(): array
    {
        return ['check_in_date' => 'date', 'checked_in_at' => 'datetime'];
    }
}
