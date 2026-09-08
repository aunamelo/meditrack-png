<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DatabaseResetController extends Controller
{
    public function index(): View
    {
        return view('admin.database-reset');
    }

    public function reset(Request $request): RedirectResponse
    {
        $request->validate([
            'confirmation' => 'required|string|in:RESET_DATABASE',
        ]);

        // Disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        // Helper function to safely truncate table
        $safeTruncate = function ($table) {
            if (DB::getSchemaBuilder()->hasTable($table)) {
                DB::table($table)->truncate();
            }
        };

        // Truncate child tables first (those with foreign keys)
        $safeTruncate('dispensing_records');
        $safeTruncate('hospital_order_items');
        $safeTruncate('hospital_shipments');
        $safeTruncate('drug_issuances');
        $safeTruncate('drug_returns');
        $safeTruncate('drug_usages');
        $safeTruncate('ward_stocks');
        $safeTruncate('stock_transfers');
        $safeTruncate('stock_adjustments');
        $safeTruncate('discrepancy_reports');
        $safeTruncate('notifications');
        $safeTruncate('sessions');

        // Truncate parent tables
        $safeTruncate('drugs');
        $safeTruncate('orders');
        $safeTruncate('patients');
        $safeTruncate('hospital_orders');
        $safeTruncate('wards');

        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        return redirect()->route('admin.dashboard.database-reset.index')
            ->with('success', 'Database has been reset successfully. All data except users, medicines, and vehicles has been removed.');
    }
}
