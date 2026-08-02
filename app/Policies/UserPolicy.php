<?php

namespace App\Policies;

use App\Models\User;
use App\Services\UserManagementService;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return UserManagementService::canManageUsers($user);
    }

    public function view(User $user, User $model): bool
    {
        return UserManagementService::canManage($user, $model);
    }

    public function create(User $user): bool
    {
        return UserManagementService::canManageUsers($user);
    }

    public function update(User $user, User $model): bool
    {
        return UserManagementService::canManage($user, $model);
    }

    public function delete(User $user, User $model): bool
    {
        return UserManagementService::canManage($user, $model);
    }

    public function restore(User $user, User $model): bool
    {
        return UserManagementService::canManage($user, $model);
    }
}
