<?php

namespace App\Mail;

use App\Models\TicketReply;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TicketRepliedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public TicketReply $reply,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Balasan baru pada Ticket #' . $this->reply->ticket->ticket_number,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.ticket-reply',
            with: [
                'reply' => $this->reply,
                'ticket' => $this->reply->ticket,
                'url' => url('/tickets/' . $this->reply->ticket->id),
            ],
        );
    }
}
