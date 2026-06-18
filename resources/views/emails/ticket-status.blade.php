{{-- resources/views/emails/ticket-status.blade.php --}}
<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><style>
    body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; line-height: 1.6; color: #1a1a2e; }
    .container { max-width: 600px; margin: 0 auto; padding: 24px; }
    .header { background: #059669; color: white; padding: 24px; border-radius: 8px 8px 0 0; }
    .body { background: #fff; padding: 24px; border: 1px solid #e5e7eb; text-align: center; }
    .status-old { display: inline-block; padding: 4px 12px; border-radius: 999px; font-size: 12px; font-weight: 600; background: #f3f4f6; color: #6b7280; text-decoration: line-through; }
    .status-new { display: inline-block; padding: 4px 12px; border-radius: 999px; font-size: 12px; font-weight: 600; background: #d1fae5; color: #065f46; }
    .arrow { font-size: 24px; margin: 0 12px; color: #9ca3af; }
    .btn { display: inline-block; padding: 10px 20px; background: #059669; color: white; text-decoration: none; border-radius: 6px; font-weight: 600; }
</style></head>
<body>
    <div class="container">
        <div class="header">
            <h2 style="margin:0">Status Ticket Diperbarui</h2>
        </div>
        <div class="body">
            <p>Halo <strong>{{ $ticket->user->name }}</strong>,</p>
            <p>Status ticket <strong>#{{ $ticket->ticket_number }}</strong> telah berubah:</p>

            <p style="margin:20px 0;">
                <span class="status-old">{{ ucfirst($oldStatus) }}</span>
                <span class="arrow">→</span>
                <span class="status-new">{{ ucfirst($newStatus) }}</span>
            </p>

            <p><strong>{{ $ticket->subject }}</strong></p>

            <p style="margin-top:24px;">
                <a href="{{ $url }}" class="btn">Lihat Ticket</a>
            </p>
        </div>
    </div>
</body>
</html>
