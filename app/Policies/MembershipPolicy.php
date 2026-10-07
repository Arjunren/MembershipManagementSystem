<?php

namespace App\Policies;

use App\Models\Membership;
use App\Models\User;

class MembershipPolicy
{
    public function view(User $user, Membership $membership): bool
    {
        return $user->isStaff() || $membership->member_id === $user->id;
    }
}
