@extends('layouts.print')

@section('title', 'Pick list '.$hospitalOrder->order_number.' · MediTrack PNG')

@section('content')
    <p class="brand">MediTrack PNG · Lae AMS Warehouse</p>
    <h1>Pick list</h1>
    <p class="meta">
        {{ $hospitalOrder->order_number }}
        · Modilon Hospital
        · {{ hospitalOrderStatusLabel($hospitalOrder->status) }}
        · FEFO (earliest expiry first)
    </p>

    <div class="grid">
        <div class="card">
            <div class="label">Order</div>
            <div class="value" style="font-size:16px">{{ $hospitalOrder->order_number }}</div>
        </div>
        <div class="card">
            <div class="label">Lines</div>
            <div class="value">{{ $hospitalOrder->items->count() }}</div>
        </div>
        <div class="card">
            <div class="label">Total units to pick</div>
            <div class="value">{{ number_format($hospitalOrder->totalQuantityApproved()) }}</div>
        </div>
        <div class="card">
            <div class="label">Reviewed by</div>
            <div class="value" style="font-size:16px">{{ $hospitalOrder->reviewer->name ?? '—' }}</div>
        </div>
    </div>

    @if($hospitalOrder->stockTransfer)
        <p class="meta">
            Road delivery {{ $hospitalOrder->stockTransfer->transfer_number }}
            @if($hospitalOrder->stockTransfer->vehicle)
                · Vehicle {{ $hospitalOrder->stockTransfer->vehicle->displayLabel() }}
            @endif
            @if($hospitalOrder->stockTransfer->expected_arrival_at)
                · ETA {{ $hospitalOrder->stockTransfer->formatExpectedArrival() }}
            @endif
        </p>
    @endif

    <h2>Pick lines</h2>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Medicine</th>
                <th>Batch</th>
                <th>Expiry</th>
                <th>Qty</th>
                <th>Loc / notes</th>
                <th>Picked</th>
            </tr>
        </thead>
        <tbody>
            @foreach($hospitalOrder->items as $index => $item)
                @php $batch = $item->sourceDrug; @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>
                        <strong>{{ $item->displayLabel() }}</strong>
                    </td>
                    <td>{{ $batch?->batch_number ?? '—' }}</td>
                    <td>{{ $batch?->expiry_date ? $batch->expiry_date->format('d M Y') : '—' }}</td>
                    <td>{{ $item->quantity_approved !== null ? number_format($item->quantity_approved) : '—' }}</td>
                    <td style="min-width:120px">&nbsp;</td>
                    <td style="min-width:72px">☐</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <h2>Sign-off</h2>
    <table>
        <thead>
            <tr>
                <th>Role</th>
                <th>Name</th>
                <th>Signature</th>
                <th>Time</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Picker</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td>Checker</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td>Dispatcher</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
            </tr>
        </tbody>
    </table>

    @if($hospitalOrder->notes)
        <h2>Order notes</h2>
        <p>{{ $hospitalOrder->notes }}</p>
    @endif
@endsection
