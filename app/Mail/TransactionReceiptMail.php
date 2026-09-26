<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TransactionReceiptMail extends Mailable
{
    use Queueable, SerializesModels;

    // The audit model (wallet_audit)
    public $audit;
    public $user;
    public $appName;

    /**
     * Create a new message instance.
     *
     * @param  mixed  $audit  // wallet_audit model instance returned from create()
     */
    public function __construct($audit)
    {
        $this->audit   = $audit;
        $this->user    = $audit->wallet?->user ?? auth()->user() ?? null;
        $this->appName = config('app.name', 'FatoriTechHub');
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $subject = sprintf("[%s] Receipt: %s", $this->appName, $this->audit->reference ?? 'Receipt');

        return $this->subject($subject)
                    ->view('emails.transaction_receipt')
                    ->with([
                        'audit'   => $this->audit,
                        'user'    => $this->user,
                        'appName' => $this->appName,
                    ]);
    }
}
