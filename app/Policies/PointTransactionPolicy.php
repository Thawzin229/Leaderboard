<?php

namespace App\Policies;

use App\Models\PointTransaction;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PointTransactionPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->is_admin;
    }

    public function view(User $user, PointTransaction $transaction): bool
    {
        if ($user->is_admin) {
            return true;
        }

        return $user->id === $transaction->user_id;
    }
    public function create(User $user): bool
    {
        return $user->is_admin;
    }

    public function update(User $user, PointTransaction $transaction): bool
    {
        return $user->is_admin;
    }

    public function delete(User $user, PointTransaction $transaction): bool
    {
        return $user->is_admin;
    }

    public function export(User $user): bool
    {
        return $user->is_admin;
    }
}