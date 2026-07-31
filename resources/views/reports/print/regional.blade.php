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
            ['Lae AMS batches', $report['inventory']['total_batches']],
            ['Units on hand', number_format($report['inventory']['total_units'])],
            ['Low stock batches', $report['inventory']['low_stock_batches']],
            ['Expiring soon', $report['inventory']['expiring_soon']],
            ['Hospital orders', $report['hospital_orders']['total']],
            ['Pending hospital orders', $report['hospital_orders']['pending']],
            ['NDoH receipts', $report['ndoh_receipts']['received'].' / '.$report['ndoh_receipts']['total']],
            ['Hospital road deliveries', $report['hospital_shipments']['total']],
            ['Units to hospital', number_format($report['hospital_shipments']['units_sent'])],
            ['NDoH units received', number_format($report['ndoh_receipts']['units_received'])],
            ['Open discrepancies', $report['discrepancies']['open']],
            ['Resolved discrepancies', $report['discrepancies']['resolved']],
        ] as [$label, $value])
            <div class="card">
                <div class="label">{{ $label }}</div>
                <div class="value">{{ $value }}</div>
            </div>
        @endforeach
    </div>

    <h2>Hospital order status</h2>
    <table>
        <thead><tr><th>Status</th><th>Count</th></tr></thead>
        <tbody>
            @foreach($report['hospital_orders'] as $key => $count)
                @if($key !== 'total')
                    <tr><td>{{ ucfirst(str_replace('_', ' ', $key)) }}</td><td>{{ $count }}</td></tr>
                @endif
            @endforeach
        </tbody>
    </table>

    <h2>Recent hospital orders</h2>
    <table>
        <thead>
            <tr>
                <th>Order</th>
                <th>Medicine</th>
                <th>Qty</th>
                <th>Status</th>
                <th>Requested</th>
            </tr>
        </thead>
        <tbody>
            @forelse($report['recent_hospital_orders'] as $order)
                <tr>
                    <td>{{ $order->order_number }}</td>
                    <td>{{ $order->drug_name }} {{ $order->dosage }}</td>
                    <td>{{ number_format($order->quantity_requested) }}</td>
                    <td>{{ ucfirst($order->status) }}</td>
                    <td>{{ $order->created_at?->format('M d, Y') }}</td>
                </tr>
            @empty
                <tr><td colspan="5">No hospital orders in this period.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>Recent hospital shipments</h2>
    <table>
        <thead>
            <tr>
                <th>Shipment</th>
                <th>Medicine</th>
                <th>Qty</th>
                <th>Status</th>
                <th>Sent</th>
            </tr>
        </thead>
        <tbody>
            @forelse($report['recent_shipments'] as $shipment)
                <tr>
                    <td>{{ $shipment->transfer_number }}</td>
                    <td>{{ $shipment->drug->drug_name ?? $shipment->hospitalOrder->drug_name ?? '—' }}</td>
                    <td>{{ number_format($shipment->quantity_sent) }}</td>
                    <td>{{ ucfirst($shipment->status) }}</td>
                    <td>{{ $shipment->sent_date?->format('M d, Y') }}</td>
                </tr>
            @empty
                <tr><td colspan="5">No hospital shipments in this period.</td></tr>
            @endforelse
        </tbody>
    </table>
@endsection
