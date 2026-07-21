<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class UserManagementService
{
    /**
     * Roles an actor may assign when creating or editing users.
     *
     * @return list<string>
     */
    public static function assignableRolesFor(User $actor): array
    {
        if ($actor->hasRole('admin')) {
            return ['procurement_officer', 'store_manager', 'pharmacy_manager'];
        }

        if ($actor->hasRole('pharmacy_manager')) {
            return ['pharmacist'];
        }

        return [];
    }

    /**
     * Whether the actor may manage user accounts at all.
     */
    public static function canManageUsers(User $actor): bool
    {
        return self::assignableRolesFor($actor) !== [];
    }

    /**
     * Users the actor is allowed to view, edit, or delete.
     */
    public static function manageableUsersQueryFor(User $actor): Builder
    {
        $assignable = self::assignableRolesFor($actor);

        $query = User::query()
            ->with('roles')
            ->whereHas('roles', fn (Builder $q) => $q->whereIn('name', $assignable));

        if ($actor->hasRole('admin')) {
            $query->whereDoesntHave('roles', fn (Builder $q) => $q->whereIn('name', ['admin', 'pharmacist']));
        }

        return $query->orderBy('name');
    }

    /**
     * Whether the actor may manage a specific user account.
     */
    public static function canManage(User $actor, User $target): bool
    {
        if ($actor->id === $target->id) {
            return false;
        }

        $assignable = self::assignableRolesFor($actor);

        if ($assignable === []) {
            return false;
        }

        $targetRoles = $target->getRoleNames()->all();

        if ($actor->hasRole('admin')) {
            if (array_intersect($targetRoles, ['admin', 'pharmacist']) !== []) {
                return false;
            }

            return array_intersect($targetRoles, $assignable) !== [];
        }

        if ($actor->hasRole('pharmacy_manager')) {
            return count($targetRoles) === 1 && in_array('pharmacist', $targetRoles, true);
        }

        return false;
    }

    /**
     * Human-readable labels for assignable roles.
     *
     * @return array<string, string>
     */
    public static function assignableRoleOptionsFor(User $actor): array
    {
        $roles = config('portal.roles', []);

        return collect(self::assignableRolesFor($actor))
            ->mapWithKeys(fn (string $role) => [$role => $roles[$role]['label'] ?? ucfirst(str_replace('_', ' ', $role))])
            ->all();
    }
}
