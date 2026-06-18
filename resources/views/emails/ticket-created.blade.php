{{-- resources/views/emails/ticket-created.blade.php --}}
<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><style>
    body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; line-height: 1.6; color: #1a1a2e; }
    .container { max-width: 600px; margin: 0 auto; padding: 24px; }
    .header { background: #059669; color: white; padding: 24px; border-radius: 8px 8px 0 0; }
    .body { background: #fff; padding: 24px; border: 1px solid #e5e7eb; }
    .status-badge { display: inline-block; padding: 4px 12px; border-radius: 999px; font-size: 12px; font-weight: 600; background: #fef3c7; color: #92400e; }
    .btn { display: inline-block; padding: 10px 20px; background: #059669; color: white; text-decoration: none; border-radius: 6px; font-weight: 600; }
</style></head>
<body>
    <div class="container">
        <div class="header">
            <h2 style="margin:0">Ticket #{{ $ticket->ticket_number }}</h2>
        </div>
        <div class="body">
            @if ($recipientType === 'client')
                <p>Halo <strong>{{ $ticket->user->name }}</strong>,</p>
                <p>Ticket Anda telah berhasil dibuat dengan detail berikut:</p>
            @else
                <p>Halo Admin/IT-Ops,</p>
                <p>Ticket baru telah dibuat oleh <strong>{{ $ticket->user->name }}</strong>:</p>
            @endif

            <table style="width:100%; border-collapse:collapse; margin:16px 0;">
                <tr><td style="padding:8px; font-weight:600; width:120px">Subject</td><td style="padding:8px">{{ $ticket->subject }}</td></tr>
                <tr><td style="padding:8px; font-weight:600">Status</td><td style="padding:8px"><span class="status-badge">{{ ucfirst($ticket->status) }}</span></td></tr>
                <tr><td style="padding:8px; font-weight:600">Prioritas</td><td style="padding:8px">{{ ucfirst($ticket->priority) }}</td></tr>
            </table>

            <p><strong>Deskripsi:</strong></p>
            <p style="background:#f9fafb; padding:12px; border-radius:6px;">{{ $ticket->description }}</p>

            <p style="text-align:center; margin-top:24px;">
                <a href="{{ $url }}" class="btn">Lihat Ticket</a>
            </p>
        </div>
    </div>
</body>
</html>
