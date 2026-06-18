<?php

namespace App\Mail;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TicketCreatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Ticket $ticket,
        public string $recipientType, // 'client' or 'staff'
    ) {}

    public function envelope(): Envelope
    {
        $subject = $this->recipientType === 'client'
            ? 'Ticket #' . $this->ticket->ticket_number . ' - Berhasil Dibuat'
            : 'Ticket Baru: #' . $this->ticket->ticket_number . ' - ' . $this->ticket->subject;

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.ticket-created',
            with: [
                'ticket' => $this->ticket,
                'recipientType' => $this->recipientType,
                'url' => $this->recipientType === 'client'
                    ? url('/tickets/' . $this->ticket->id)
                    : url('/admin/tickets/' . $this->ticket->id),
            ],
        );
    }
}
