<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class TestMailConfigurationMail extends Mailable
{
    public function __construct(
        public string $messageLocale,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('messages.mail.test_subject', [], $this->messageLocale),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.admin.test-mail-settings',
            with: [
                'locale' => $this->messageLocale,
            ],
        );
    }
}
