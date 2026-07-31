@extends('layouts.print')

@section('title', $report['title'].' · MediTrack PNG')

@section('content')
    <p class="brand">MediTrack PNG</p>
    <h1>{{ $report['title'] }}</h1>
    <p class="meta">
        {{ $report['facility'] }} · {{ $report['period']['from'] }} — {{ $report['period']['to'] }}
    </p>

    <div class="grid">
        @foreach([
            ['NDoH batches', $report['inventory']['total_batches']],
            ['NDoH units', number_format($report['inventory']['total_units'])],
            ['Low stock', $report['inventory']['low_stock_batches']],
            ['Expiring soon', $report['inventory']['expiring_soon']],
            ['Procurement orders', $report['orders']['total']],
            ['Orders pending', $report['orders']['pending']],
            ['Orders received', $report['orders']['received']],
            ['Units ordered', number_format($report['orders']['units_ordered'])],
            ['Shipments to Lae', $report['shipments']['total']],
            ['Awaiting approval', $report['shipments']['pending']],
            ['In transit', $report['shipments']['sent']],
            ['Received at Lae', $report['shipments']['received']],
            ['Lae AMS units', number_format($report['corridor']['lae_ams_units'])],
            ['Modilon units', number_format($report['corridor']['modilon_units'])],
            ['Supplier units received', number_format($report['orders']['units_received'])],
            ['Stock takes', $report['stock_takes']['total']],
        ] as [$label, $value])
            <div class="card">
                <div class="label">{{ $label }}</div>
                <div class="value">{{ $value }}</div>
            </div>
        @endforeach
    </div>

    <h2>Procurement pipeline</h2>
    <table>
        <thead><tr><th>Status</th><th>Count</th></tr></thead>
        <tbody>
            @foreach(['pending','manufacturing','shipped','customs','fx_cleared','partial','received','cancelled'] as $status)
                <tr>
                    <td>{{ str_replace('_', ' ', ucfirst($status)) }}</td>
                    <td>{{ $report['orders'][$status] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <h2>Recent procurement orders</h2>
    <table>
        <thead>
            <tr>
                <th>Order</th>
                <th>Items</th>
                <th>Status</th>
                <th>Created by</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            @forelse($report['recent_orders'] as $order)
                <tr>
                    <td>{{ $order->order_number }}</td>
                    <td>{{ $order->itemsSummary() }}</td>
                    <td>{{ $order->statusLabel() }}</td>
                    <td>{{ $order->creator->name ?? '—' }}</td>
                    <td>{{ $order->created_at?->format('M d, Y') }}</td>
                </tr>
            @empty
                <tr><td colspan="5">No procurement orders in this period.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>Recent shipments to Lae AMS</h2>
    <table>
        <thead>
            <tr>
                <th>Shipment</th>
                <th>Medicine</th>
                <th>Qty</th>
                <th>Status</th>
                <th>Ship date</th>
            </tr>
        </thead>
        <tbody>
            @forelse($report['recent_shipments'] as $shipment)
                <tr>
                    <td>{{ $shipment->transfer_number }}</td>
                    <td>{{ $shipment->drug->drug_name ?? '—' }}</td>
                    <td>{{ number_format($shipment->quantity_sent) }}</td>
                    <td>{{ ndohToLaeAmsTransferStatusLabel($shipment->status) }}</td>
                    <td>{{ $shipment->sent_date?->format('M d, Y') }}</td>
                </tr>
            @empty
                <tr><td colspan="5">No shipments to Lae AMS in this period.</td></tr>
            @endforelse
        </tbody>
    </table>
@endsection
