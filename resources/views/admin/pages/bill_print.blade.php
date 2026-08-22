@extends('layouts.print_receipt')

@section('title', $bill ? 'Hóa đơn HD#' . $bill->id_bill : 'Tạm tính đơn #' . ($order->id_order ?? ''))

@section('content')
    @php
        $payLabel = match($bill->pay_method ?? '') {
            'cash'     => 'TIEN MAT',
            'card'     => 'THE',
            'transfer' => 'CHUYEN KHOAN',
            'qr'       => 'QR CODE',
            default    => $bill->pay_method ? strtoupper($bill->pay_method) : '—',
        };

        $storeName = $order->branch->name ?? 'NHA HANG';
        $storeAddr = $order->branch->address ?? '';
        $isPaid    = $bill && (int) $bill->status !== 1;

        $payTime = $bill && $bill->pay_time
            ? \Carbon\Carbon::parse($bill->pay_time)
            : ($order->order_time ? \Carbon\Carbon::parse($order->order_time) : \Carbon\Carbon::now());
    @endphp

    {{-- ====== Tiêu đề nhà hàng ====== --}}
    <div class="center">
        <div class="store-name">{{ $storeName }}</div>
        @if($storeAddr)
            <div class="small">{{ $storeAddr }}</div>
        @endif
        <div class="small">Hotline: {{ $order->branch->phone ?? '—' }}</div>
    </div>

    <hr class="divider">

    <div class="center bold">
        {{ $isPaid ? 'HOA DON BAN HANG' : 'PHIEU TAM TINH' }}
    </div>
    <div class="center small">
        @if($bill)
            #HD{{ $bill->id_bill }}
        @else
            #{{ $order->id_order ?? '—' }}
        @endif
        &nbsp;|&nbsp; {{ $payTime->format('d/m/Y H:i') }}
    </div>

    <hr class="divider">

    {{-- ====== Thông tin đơn ====== --}}
    <div class="info-line">
        <span>Ban:</span>
        <span class="bold">{{ $order->table->table_name ?? '—' }}</span>
    </div>
    <div class="info-line">
        <span>NV:</span>
        <span>{{ $order->user->name ?? '—' }}</span>
    </div>
    <div class="info-line">
        <span>Khach:</span>
        <span>{{ $order->customer->fullname ?? ($order->customer_name ?? '—') }}</span>
    </div>
    @if($order->customer && $order->customer->phone)
        <div class="info-line">
            <span>SDT:</span>
            <span>{{ $order->customer->phone }}</span>
        </div>
    @endif

    <hr class="divider">

    {{-- ====== Danh sách món ====== --}}
    <table class="items">
        <thead>
            <tr>
                <th>Mon</th>
                <th class="qty">SL</th>
                <th class="price">Don gia</th>
                <th class="total">T.tien</th>
            </tr>
        </thead>
        <tbody>
            @forelse($grouped as $detail)
                @php
                    $lineTotal = (float) $detail->price * (int) $detail->quantity;
                @endphp
                <tr>
                    <td class="item-name">
                        {{ $detail->menuItem->name ?? ('Mon #' . $detail->id_food) }}
                    </td>
                    <td class="qty">{{ (int) $detail->quantity }}</td>
                    <td class="price">{{ number_format((float) $detail->price, 0, ',', '.') }}</td>
                    <td class="total">{{ number_format($lineTotal, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="center small">Don hang chua co mon nao</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <hr class="divider">

    {{-- ====== Tổng tiền ====== --}}
    <div class="totals">
        <div class="row">
            <span>Tam tinh:</span>
            <span>{{ number_format($subtotal, 0, ',', '.') }}</span>
        </div>
        @if(!empty($bill) && (float) ($bill->discount_percent ?? 0) > 0)
            <div class="row">
                <span>Giam gia ({{ rtrim(rtrim(number_format((float) $bill->discount_percent, 1), '0'), '.') }}%):</span>
                <span>-{{ number_format(((float) $bill->discount_percent / 100) * $subtotal, 0, ',', '.') }}</span>
            </div>
        @endif
        <div class="row grand">
            <span>TONG CONG:</span>
            <span>{{ number_format($subtotal, 0, ',', '.') }} d</span>
        </div>
        @if($isPaid)
            <div class="row">
                <span>Thanh toan:</span>
                <span>{{ $payLabel }}</span>
            </div>
        @endif
    </div>

    <hr class="divider">

    {{-- ====== Footer ====== --}}
    <div class="footer-note">
        @if($isPaid)
            <div class="bold">Cam on quy khach!</div>
            <div class="xsmall">Hen gap lai quy khach lan sau.</div>
        @else
            <div class="bold">Phieu tam tinh</div>
            <div class="xsmall">Chua thanh toan.</div>
        @endif
        <div class="xsmall" style="margin-top:4px;">
            In luc: {{ \Carbon\Carbon::now()->format('d/m/Y H:i:s') }}
        </div>
    </div>
@endsection
