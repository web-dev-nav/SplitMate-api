<?php

namespace App\Mail;

use App\Models\Group;
use App\Models\Settlement;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SettlementCreatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Settlement $settlement,
        public readonly Group $group,
        public readonly User $recipient,
        public readonly array $recipientSnapshot,
    ) {}

    public function envelope(): Envelope
    {
        $fromName = $this->settlement->fromUser?->name ?? 'Someone';
        $toName = $this->settlement->toUser?->name ?? 'Someone';

        return new Envelope(
            subject: "New settlement in \"{$this->group->name}\": {$fromName} paid {$toName}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.settlement-created',
            with: [
                'settlement' => $this->settlement,
                'group' => $this->group,
                'recipient' => $this->recipient,
                'recipientSnapshot' => $this->recipientSnapshot,
                'amountFormatted' => number_format(((int) $this->settlement->amount_cents) / 100, 2),
            ],
        );
    }
}
