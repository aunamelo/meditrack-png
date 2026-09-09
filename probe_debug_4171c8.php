<?php

/**
 * Temporary system probe for debug session 4171c8.
 * Writes NDJSON to debug-4171c8.log — do not commit.
 */

require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$logPath = __DIR__.'/debug-4171c8.log';

$log = function (string $hypothesisId, string $location, string $message, array $data = []) use ($logPath) {
    $payload = [
        'sessionId' => '4171c8',
        'runId' => 'probe-pre',
        'hypothesisId' => $hypothesisId,
        'location' => $location,
        'message' => $message,
        'data' => $data,
        'timestamp' => (int) (microtime(true) * 1000),
    ];
    file_put_contents($logPath, json_encode($payload)."\n", FILE_APPEND);
};

use App\Models\HospitalOrder;
use App\Models\StockTransfer;
use App\Models\Drug;
use App\Services\HospitalShipmentService;
use Illuminate\Support\Facades\Schema;

// H1: ship throws InvalidArgumentException for non-shippable order
$nonShippable = HospitalOrder::query()->whereNotIn('status', ['approved'])->first()
    ?? HospitalOrder::query()->first();
if ($nonShippable) {
    try {
        HospitalShipmentService::ship($nonShippable, 1, 1, 'debug-probe');
        $log('H1', 'probe.php:ship', 'ship did not throw', ['order_id' => $nonShippable->id, 'status' => $nonShippable->status]);
    } catch (\InvalidArgumentException $e) {
        $log('H1', 'probe.php:ship', 'InvalidArgumentException thrown as expected', [
            'order_id' => $nonShippable->id,
            'status' => $nonShippable->status,
            'exception' => $e->getMessage(),
            'controller_catches' => false,
        ]);
    } catch (\Throwable $e) {
        $log('H1', 'probe.php:ship', 'other exception', [
            'class' => get_class($e),
            'message' => $e->getMessage(),
        ]);
    }
} else {
    $log('H1', 'probe.php:ship', 'no hospital orders to probe', []);
}

// H2: multi-item road transfers vs controller eager-load
$multi = StockTransfer::query()
    ->where('from_level', 'lae_ams')
    ->where('to_level', 'modilon_hospital')
    ->has('items', '>', 1)
    ->with(['drug', 'items.drug'])
    ->first();

$log('H2', 'probe.php:hospital-shipments', 'multi-item road delivery sample', [
    'found' => (bool) $multi,
    'transfer_id' => $multi?->id,
    'header_drug' => $multi?->drug?->drug_name,
    'item_count' => $multi?->items?->count(),
    'medicines_label' => $multi?->medicinesLabel(),
    'index_would_show' => $multi?->drug?->drug_name ?? 'N/A',
    'controller_loads_items' => false,
]);

// H3: dosage-only mismatch possible (name match, dosage differ)
$item = \App\Models\HospitalOrderItem::query()->first();
if ($item) {
    $sameNameDiffDosage = Drug::query()
        ->where('level', 'lae_ams')
        ->where('drug_name', $item->drug_name)
        ->where('dosage', '!=', $item->dosage)
        ->count();
    $log('H3', 'probe.php:approve-match', 'same-name different-dosage Lae batches', [
        'order_item_id' => $item->id,
        'item_name' => $item->drug_name,
        'item_dosage' => $item->dosage,
        'alt_dosage_batches' => $sameNameDiffDosage,
        'validator_checks_dosage' => false,
    ]);
} else {
    $log('H3', 'probe.php:approve-match', 'no hospital_order_items', []);
}

// H4: schema + welcome asset
$log('H4', 'probe.php:schema-assets', 'schema and asset checks', [
    'hospital_order_items' => Schema::hasTable('hospital_order_items'),
    'stock_transfer_items' => Schema::hasTable('stock_transfer_items'),
    'health_workers_jpg' => file_exists(__DIR__.'/public/assets/health-workers.jpg'),
    'vite_manifest' => file_exists(__DIR__.'/public/build/manifest.json'),
]);

// H5: NDoH transfers without items
$ndohNoItems = StockTransfer::query()
    ->whereNull('hospital_order_id')
    ->where('from_level', 'ndoh')
    ->doesntHave('items')
    ->count();
$ndohWithItems = StockTransfer::query()
    ->whereNull('hospital_order_id')
    ->where('from_level', 'ndoh')
    ->has('items')
    ->count();
$log('H5', 'probe.php:ndoh-items', 'NDoH transfer item coverage', [
    'without_items' => $ndohNoItems,
    'with_items' => $ndohWithItems,
]);

echo "Probe complete. Logged to debug-4171c8.log\n";
