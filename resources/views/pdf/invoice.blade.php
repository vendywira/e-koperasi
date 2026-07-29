<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; color: #334155; line-height: 1.5; }
        .header { border-bottom: 2px solid #059669; padding-bottom: 15px; margin-bottom: 25px; overflow: auto; }
        .header h1 { color: #059669; margin: 0; font-size: 26px; float: left; }
        .header .info { float: right; text-align: right; font-size: 11px; }
        .header .info p { margin: 2px 0; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th { background: #059669; color: white; padding: 10px 8px; text-align: left; font-size: 10px; text-transform: uppercase; }
        td { padding: 8px; border-bottom: 1px solid #e2e8f0; }
        tr:nth-child(even) td { background: #f8fafc; }
        .totals { text-align: right; margin-top: 25px; padding-right: 8px; }
        .totals .row { margin: 4px 0; }
        .grand-total { font-size: 16px; font-weight: bold; color: #059669; border-top: 2px solid #059669; padding-top: 8px; }
        .status-paid { color: #059669; font-weight: bold; }
        .status-pending { color: #d97706; font-weight: bold; }
        .footer { margin-top: 40px; padding-top: 15px; border-top: 1px solid #e2e8f0; font-size: 9px; color: #94a3b8; text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <h1>INVOICE</h1>
        <div class="info">
            @if($companyName ?? false)<p><strong>{{ $companyName }}</strong></p>@endif
            <p>No. {{ $invoice->invoice_number }}</p>
            <p>Tanggal: {{ $invoice->created_at->format('d M Y') }}</p>
            <p>Jatuh Tempo: {{ $invoice->due_date ? $invoice->due_date->format('d M Y') : '-' }}</p>
            <p>Status: <span class="status-{{ $invoice->status }}">{{ strtoupper($invoice->status) }}</span></p>
        </div>
    </div>

    <p><strong>Tagihan untuk:</strong> {{ $invoice->name }} ({{ $invoice->domain }})</p>

    <table>
        <thead>
            <tr><th>Deskripsi</th><th>Jumlah</th><th>Harga</th><th>Diskon</th><th>Total</th></tr>
        </thead>
        <tbody>
            @forelse($invoice->invoiceItems as $item)
            <tr>
                <td>{{ $item->description }}</td>
                <td>{{ $item->quantity }}</td>
                <td>Rp{{ number_format($item->unit_price, 0, ',', '.') }}</td>
                <td>Rp{{ number_format($item->discount_amount, 0, ',', '.') }}</td>
                <td style="text-align:right">Rp{{ number_format($item->total_amount, 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr><td colspan="5" style="text-align:center; padding:20px; color:#94a3b8; font-style:italic;">Belum ada rincian item untuk invoice ini.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="totals">
        @if($invoice->subtotal)
        <div class="row">Subtotal: Rp{{ number_format($invoice->subtotal, 0, ',', '.') }}</div>
        @endif
        @if(($invoice->discount_amount ?? 0) > 0)
        <div class="row">Diskon: -Rp{{ number_format($invoice->discount_amount, 0, ',', '.') }}</div>
        @endif
        <div class="row grand-total">Total: Rp{{ number_format($invoice->total_amount, 0, ',', '.') }}</div>
    </div>

    <div class="footer">
        {{ $companyName ?? 'e-Koperasi' }} — Dokumen digenerate otomatis.
    </div>
</body>
</html>