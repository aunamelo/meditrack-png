<?php

namespace App\Services;

use App\Models\Drug;
use App\Models\HospitalOrder;
use App\Models\Order;
use App\Models\User;

class ProfileService
{
    /**
     * Role-aware activity summary for the profile overview.
     *
     * @return array<int, array{label: string, value: string, hint: string}>
     */
    public static function activitySummary(User $user): array
    {
        if ($user->hasRole('admin')) {
            $pending = Order::pending()->count();

            return [
                ['label' => 'Pending approvals', 'value' => (string) $pending, 'hint' => 'Procurement orders awaiting NDoH sign-off'],
                ['label' => 'In import pipeline', 'value' => (string) Order::inPipeline()->count(), 'hint' => 'Manufacturing through FX cleared'],
            ];
        }

        if ($user->hasRole('procurement_officer')) {
            return [
                ['label' => 'My pending orders', 'value' => (string) Order::pending()->where('created_by', $user->id)->count(), 'hint' => 'Awaiting NDoH approval'],
                ['label' => 'Orders submitted', 'value' => (string) Order::where('created_by', $user->id)->count(), 'hint' => 'Total procurement orders'],
            ];
        }

        if ($user->hasRole('store_manager')) {
            return [
                ['label' => 'Hospital orders', 'value' => (string) HospitalOrder::pending()->count(), 'hint' => 'Modilon requests awaiting review'],
                ['label' => 'Low stock batches', 'value' => (string) Drug::atLevel('lae_ams')->lowStock()->count(), 'hint' => 'At Lae AMS warehouse'],
            ];
        }

        if ($user->hasRole('pharmacy_manager')) {
            $level = 'modilon_hospital';

            return [
                ['label' => 'Open requests', 'value' => (string) HospitalOrder::where('requested_by', $user->id)->pending()->count(), 'hint' => 'Awaiting Lae AMS'],
                ['label' => 'Low stock batches', 'value' => (string) Drug::atLevel($level)->lowStock()->count(), 'hint' => 'At Modilon Hospital'],
            ];
        }

        if ($user->hasRole('pharmacist')) {
            $level = 'modilon_hospital';

            return [
                ['label' => 'Available batches', 'value' => (string) Drug::atLevel($level)->inInventory()->count(), 'hint' => 'Ready to dispense'],
                ['label' => 'Expiring soon', 'value' => (string) Drug::atLevel($level)->expiring()->count(), 'hint' => 'Within 6 months'],
            ];
        }

        return [];
    }
}
