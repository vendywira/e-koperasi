<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DemoRequestMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public array $data) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Demo Request dari ' . $this->data['cooperative_name'],
            replyTo: [$this->data['whatsapp'] . '@whatsapp.com'],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.demo-request',
            with: ['data' => $this->data],
        );
    }
}
