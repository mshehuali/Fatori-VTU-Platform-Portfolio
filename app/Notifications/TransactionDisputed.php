<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TransactionDisputed extends Notification implements ShouldQueue
{
    use Queueable;

    protected $audit;

    /**
     * Create a new notification instance.
     */
    public function __construct($audit)
    {
        $this->audit = $audit;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $reason = $this->audit->meta['dispute_reason'] ?? 'No reason provided';

        return (new MailMessage)
            ->subject('⚠️ Transaction Disputed — ' . $this->audit->reference)
            ->greeting('Heads up — a transaction has been disputed')
            ->line('A wallet transaction was marked as DISPUTED.')
            ->line('Reference: ' . $this->audit->reference)
            ->line('Amount: ₦' . number_format($this->audit->amount, 2))
            ->line('Reason: ' . $reason)
            ->action(
                'View Transaction',
                url(route('transactions.receipt', $this->audit->reference))
            )
            ->line('Please review this transaction and take necessary action.');
    }

    /**
     * Optional: store notification payload.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'reference' => $this->audit->reference,
            'amount'    => $this->audit->amount,
            'status'    => 'disputed',
        ];
    }
}
