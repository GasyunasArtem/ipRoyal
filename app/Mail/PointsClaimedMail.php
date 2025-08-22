<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PointsClaimedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public int $pointsClaimed,
        public float $usdEarned,
        public int $transactionCount
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Points Successfully Claimed - $' . number_format($this->usdEarned, 2) . ' Added to Your Wallet',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.points-claimed',
            with: [
                'user' => $this->user,
                'pointsClaimed' => $this->pointsClaimed,
                'usdEarned' => $this->usdEarned,
                'transactionCount' => $this->transactionCount,
                'conversionRate' => 0.01,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}