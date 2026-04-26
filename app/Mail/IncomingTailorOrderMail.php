<?php

namespace App\Mail;

use App\Models\Order;
use App\Models\User;
use App\Services\OrderFinancialsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class IncomingTailorOrderMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Order $order,
        public User $tailor,
        public string $messageLocale,
    ) {
        $this->afterCommit();
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('messages.mail.incoming_tailor_order_subject', [
                'order' => $this->order->id,
            ], $this->messageLocale),
        );
    }

    public function content(): Content
    {
        $this->order->loadMissing(['product.category', 'product.fabric']);

        return new Content(
            view: 'emails.tailor.incoming-order',
            with: [
                'order' => $this->order,
                'tailor' => $this->tailor,
                'locale' => $this->messageLocale,
                'financials' => app(OrderFinancialsService::class)->payload($this->order),
                'dashboardUrl' => url('/app/tailor/orders/'.$this->order->id),
            ],
        );
    }
}
