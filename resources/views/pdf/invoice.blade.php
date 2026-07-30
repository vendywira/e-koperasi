<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; color: #334155; line-height: 1.5; margin: 30px; }
        .header { border-bottom: 2px solid #059669; padding-bottom: 15px; margin-bottom: 25px; width: 100%; }
        .header-table { width: 100%; border-collapse: collapse; }
        .header-table td { border: none; padding: 0; vertical-align: top; }
        .header-left h1 { color: #059669; margin: 0; font-size: 26px; }
        .header-left p { margin: 2px 0; color: #64748b; font-size: 10px; }
        .header-right { text-align: right; }
        .header-right p { margin: 2px 0; font-size: 10px; }
        .header-right .label { color: #94a3b8; }
        .header-right .value { color: #334155; font-weight: bold; }
        .status-success { color: #059669; }
        .status-pending { color: #d97706; }
        .status-expired { color: #dc2626; }

        .client-info { margin-bottom: 20px; font-size: 10px; color: #475569; }

        table.items { width: 100%; border-collapse: collapse; margin: 20px 0; }
        table.items th { background: #059669; color: white; padding: 10px 8px; text-align: left; font-size: 9px; text-transform: uppercase; }
        table.items th.right { text-align: right; }
        table.items th.center { text-align: center; }
        table.items td { padding: 8px; border-bottom: 1px solid #e2e8f0; font-size: 10px; }
        table.items td.right { text-align: right; }
        table.items td.center { text-align: center; }
        table.items tr:nth-child(even) td { background: #f8fafc; }

        .totals { text-align: right; margin-top: 20px; padding-right: 4px; width: 100%; }
        .totals table { margin-left: auto; border-collapse: collapse; width: 280px; }
        .totals td { padding: 3px 8px; font-size: 10px; }
        .totals td.label { text-align: right; color: #64748b; }
        .totals td.value { text-align: right; font-weight: bold; }
        .totals .grand-total td { font-size: 14px; font-weight: bold; color: #059669; border-top: 2px solid #059669; padding-top: 8px; }
        .totals .grand-total td.label { color: #1e293b; }

        .payment-info { margin-top: 25px; border: 1px solid #e2e8f0; border-radius: 6px; padding: 15px; background: #f8fafc; }
        .payment-info h3 { margin: 0 0 10px 0; font-size: 11px; color: #1e293b; text-transform: uppercase; }
        .payment-info table { width: 100%; border-collapse: collapse; font-size: 10px; }
        .payment-info td { padding: 4px 8px; }
        .payment-info td.label { color: #64748b; width: 140px; }
        .payment-info td.value { color: #334155; font-weight: bold; }
        .payment-info .divider td { border-top: 1px solid #e2e8f0; padding-top: 8px; }

        .footer { margin-top: 30px; padding-top: 15px; border-top: 1px solid #e2e8f0; font-size: 8px; color: #94a3b8; text-align: center; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 3px; font-size: 9px; font-weight: bold; }
        .badge-success { background: #d1fae5; color: #059669; }
        .badge-pending { background: #fef3c7; color: #d97706; }
    </style>
</head>
<body>
    <table class="header-table">
        <tr>
            <td class="header-left" width="50%">
                <h1>INVOICE</h1>
                <p>{{ $companyName ?? 'e-Koperasi' }}</p>
            </td>
            <td class="header-right" width="50%">
                <p><span class="label">No.</span> <span class="value">{{ $invoice->invoice_number ?? $invoice->id }}</span></p>
                <p><span class="label">Tanggal</span> <span class="value">{{ $invoice->created_at->format('d M Y') }}</span></p>
                <p><span class="label">Jatuh Tempo</span> <span class="value">{{ $invoice->due_date ? $invoice->due_date->format('d M Y') : '-' }}</span></p>
                <p><span class="label">Status</span>
                    <span class="badge badge-{{ $invoice->status === 'paid' ? 'success' : 'pending' }}">
                        {{ $invoice->status === 'paid' ? 'LUNAS' : strtoupper($invoice->status) }}
                    </span>
                </p>
            </td>
        </tr>
    </table>

    <div class="client-info">
        <strong>Tagihan untuk:</strong> {{ $invoice->name }} ({{ $invoice->domain }})
    </div>

    <table class="items">
        <thead>
            <tr><th width="40%">Deskripsi</th><th class="center" width="12%">Jumlah</th><th class="right" width="16%">Harga</th><th class="right" width="16%">Diskon</th><th class="right" width="16%">Total</th></tr>
        </thead>
        <tbody>
            @forelse($invoice->invoiceItems as $item)
            <tr>
                <td>{{ $item->description }}</td>
                <td class="center">{{ $item->quantity }}</td>
                <td class="right">Rp{{ number_format($item->unit_price, 0, ',', '.') }}</td>
                <td class="right">-Rp{{ number_format($item->discount_amount, 0, ',', '.') }}</td>
                <td class="right">Rp{{ number_format($item->total_amount, 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr><td colspan="5" style="text-align:center; padding:20px; color:#94a3b8; font-style:italic;">Belum ada rincian item untuk invoice ini.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="totals">
        <table>
            @if($invoice->subtotal)
            <tr><td class="label">Subtotal</td><td class="value">Rp{{ number_format($invoice->subtotal, 0, ',', '.') }}</td></tr>
            @endif
            @if(($invoice->discount_amount ?? 0) > 0)
            <tr><td class="label">Diskon</td><td class="value" style="color:#dc2626;">-Rp{{ number_format($invoice->discount_amount, 0, ',', '.') }}</td></tr>
            @endif
            <tr class="grand-total"><td class="label">Total</td><td class="value">Rp{{ number_format($invoice->total_amount, 0, ',', '.') }}</td></tr>
        </table>
    </div>

    @if($payment)
    <div class="payment-info">
        <h3>Informasi Pembayaran</h3>
        <table>
            <tr><td class="label">Metode Pembayaran</td><td class="value">{{ $payment['method'] }}</td></tr>
            <tr><td class="label">Tipe Pembayaran</td><td class="value">{{ $payment['payment_type'] === 'gateway' ? 'Gateway (Duitku)' : 'Transfer Manual' }}</td></tr>
            @if($payment['fee_amount'] > 0)
            <tr><td class="label">Biaya Layanan</td><td class="value">Rp{{ number_format($payment['fee_amount'], 0, ',', '.') }}</td></tr>
            @endif
            <tr><td class="label">Jumlah Dibayar</td><td class="value">Rp{{ number_format($payment['amount'], 0, ',', '.') }}</td></tr>
            @if($payment['paid_at'])
            <tr><td class="label">Tanggal Bayar</td><td class="value">{{ $payment['paid_at'] instanceof \Carbon\Carbon ? $payment['paid_at']->format('d M Y H:i') : $payment['paid_at'] }}</td></tr>
            @endif
            @if($payment['receipt_number'])
            <tr><td class="label">No. Receipt</td><td class="value">{{ $payment['receipt_number'] }}</td></tr>
            @endif
            <tr class="divider"><td colspan="2"></td></tr>
            <tr><td class="label">Status Pembayaran</td><td class="value">
                <span class="badge badge-{{ $payment['status'] === 'success' ? 'success' : 'pending' }}">
                    {{ $payment['status'] === 'success' ? 'LUNAS' : strtoupper($payment['status']) }}
                </span>
            </td></tr>
        </table>
    </div>
    @endif

    <div class="footer">
        {{ $companyName ?? 'e-Koperasi' }} — Dokumen digenerate otomatis.
    </div>
</body>
</html>
