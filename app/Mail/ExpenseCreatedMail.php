<?php

namespace App\Mail;

use App\Models\Expense;
use App\Models\Group;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ExpenseCreatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Expense $expense,
        public readonly Group $group,
        public readonly User $recipient,
        public readonly string $paidByName,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "New expense in \"{$this->group->name}\": {$this->expense->title}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.expense-created',
            with: [
                'expense'      => $this->expense,
                'group'        => $this->group,
                'recipient'    => $this->recipient,
                'paidByName'   => $this->paidByName,
                'amountFormatted' => number_format($this->expense->amount_cents / 100, 2),
                'perPersonFormatted' => count($this->expense->participant_ids ?? []) > 0
                    ? number_format($this->expense->amount_cents / 100 / count($this->expense->participant_ids), 2)
                    : number_format($this->expense->amount_cents / 100, 2),
                'participantCount' => count($this->expense->participant_ids ?? []),
            ],
        );
    }
}
