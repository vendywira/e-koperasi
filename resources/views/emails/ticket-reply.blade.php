{{-- resources/views/emails/ticket-reply.blade.php --}}
<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><style>
    body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; line-height: 1.6; color: #1a1a2e; }
    .container { max-width: 600px; margin: 0 auto; padding: 24px; }
    .header { background: #059669; color: white; padding: 24px; border-radius: 8px 8px 0 0; }
    .body { background: #fff; padding: 24px; border: 1px solid #e5e7eb; }
    .btn { display: inline-block; padding: 10px 20px; background: #059669; color: white; text-decoration: none; border-radius: 6px; font-weight: 600; }
</style></head>
<body>
    <div class="container">
        <div class="header">
            <h2 style="margin:0">Balasan Baru — Ticket #{{ $ticket->ticket_number }}</h2>
        </div>
        <div class="body">
            <p>Halo <strong>{{ $ticket->user->name }}</strong>,</p>
            <p>Ticket <strong>#{{ $ticket->ticket_number }}</strong> (<em>{{ $ticket->subject }}</em>) mendapat balasan baru:</p>

            <div style="background:#f0fdf4; padding:16px; border-radius:8px; margin:16px 0; border-left:4px solid #059669;">
                <p style="margin:0 0 8px; font-size:13px; color:#666;">
                    <strong>{{ $reply->user->name }}</strong> ({{ ucfirst($reply->user->role) }}) — {{ $reply->created_at->format('d M Y H:i') }}
                </p>
                <p style="margin:0; white-space:pre-wrap;">{{ $reply->message }}</p>
            </div>

            <p style="text-align:center; margin-top:24px;">
                <a href="{{ $url }}" class="btn">Balas di Dashboard</a>
            </p>
        </div>
    </div>
</body>
</html>
