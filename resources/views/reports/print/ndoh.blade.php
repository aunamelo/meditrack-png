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
            ['Amount spent (PGK)', 'K '.number_format($report['spending']['amount_spent'], 2)],
            ['Committed spend', 'K '.number_format($report['spending']['amount_committed'], 2)],
            ['In pipeline', 'K '.number_format($report['spending']['amount_in_pipeline'], 2)],
            ['Pending approval', 'K '.number_format($report['spending']['amount_pending_approval'], 2)],
            ['Inventory value (now)', 'K '.number_format($report['spending']['inventory_value'], 2)],
            ['Procurement orders', $report['orders']['total']],
            ['Shipments to Lae', $report['shipments']['total']],
            ['NDoH units on hand (now)', number_format($report['inventory']['total_units'])],
        ] as [$label, $value])
            <div class="card">
                <div class="label">{{ $label }}</div>
                <div class="value">{{ $value }}</div>
            </div>
        @endforeach
    </div>

    <h2>Spend by supplier (PGK)</h2>
    <table>
        <thead><tr><th>Supplier</th><th>Orders</th><th>Amount</th></tr></thead>
        <tbody>
            @forelse($report['spending']['by_supplier'] as $row)
                <tr>
                    <td>{{ $row['supplier'] }}</td>
                    <td>{{ $row['orders'] }}</td>
                    <td>K {{ number_format($row['amount'], 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="3">No invoice amounts recorded for this period.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>Corridor stock</h2>
    <table>
        <tbody>
            <tr><td>NDoH central</td><td>{{ number_format($report['corridor']['ndoh_units']) }}</td></tr>
            <tr><td>Lae AMS</td><td>{{ number_format($report['corridor']['lae_ams_units']) }}</td></tr>
            <tr><td>Modilon Hospital</td><td>{{ number_format($report['corridor']['modilon_units']) }}</td></tr>
        </tbody>
    </table>

    <h2>Recent procurement orders</h2>
    <table>
        <thead>
            <tr>
                <th>Order</th>
                <th>Items</th>
                <th>Invoice (PGK)</th>
                <th>Status</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            @forelse($report['recent_orders'] as $order)
                <tr>
                    <td>{{ $order->order_number }}</td>
                    <td>{{ $order->itemsSummary() }}</td>
                    <td>{{ $order->invoice_amount !== null ? 'K '.number_format((float) $order->invoice_amount, 2) : '—' }}</td>
                    <td>{{ $order->statusLabel() }}</td>
                    <td>{{ $order->created_at?->format('M d, Y') }}</td>
                </tr>
            @empty
                <tr><td colspan="5">No procurement orders in this period.</td></tr>
            @endforelse
        </tbody>
    </table>
@endsection
