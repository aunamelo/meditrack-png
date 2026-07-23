<?php

namespace App\Policies;

use App\Models\Drug;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class DrugPolicy
{
    /**
     * Determine whether the user can view any models.
     * All authenticated users can view drugs at their level.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'procurement_officer', 'store_manager', 'pharmacy_manager', 'pharmacist']);
    }

    /**
     * Determine whether the user can view the model.
     * Role-based: Users can only view drugs at their assigned level.
     */
    public function view(User $user, Drug $drug): bool
    {
        // NDoH Admin can view all levels
        if ($user->hasRole('admin')) {
            return true;
        }

        // Procurement Officer can only view NDoH level drugs
        if ($user->hasRole('procurement_officer')) {
            return $drug->level === 'ndoh';
        }

        // Store Manager can only view Lae AMS level drugs
        if ($user->hasRole('store_manager')) {
            return $drug->level === 'lae_ams';
        }

        // Pharmacy Manager and Pharmacist can only view Modilon Hospital level drugs
        if ($user->hasRole('pharmacy_manager') || $user->hasRole('pharmacist')) {
            return $drug->level === 'modilon_hospital';
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     * Only Procurement Officers can create drugs (at NDoH level).
     */
    public function create(User $user): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can update the model.
     * Procurement Officer (ndoh), Pharmacy Manager (hospital), and NDoH Admin can update.
     */
    public function update(User $user, Drug $drug): bool
    {
        // NDoH Admin can update all drugs
        if ($user->hasRole('admin')) {
            return true;
        }

        // Procurement Officer can only update NDoH level drugs
        if ($user->hasRole('procurement_officer')) {
            return $drug->level === 'ndoh';
        }

        // Pharmacy Manager can only update Modilon Hospital level drugs
        if ($user->hasRole('pharmacy_manager')) {
            return $drug->level === 'modilon_hospital';
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     * Only NDoH Admin can delete drugs.
     */
    public function delete(User $user, Drug $drug): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Drug $drug): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Drug $drug): bool
    {
        return $user->hasRole('admin');
    }
}
