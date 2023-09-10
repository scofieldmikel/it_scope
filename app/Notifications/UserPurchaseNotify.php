<?php

namespace App\Notifications;

use App\Models\User;
use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use App\Mail\UserPurchaseEmail;
use App\Models\Auth\Transaction;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class UserPurchaseNotify extends Notification implements ShouldQueue
{
    use Queueable;

    public Transaction $transaction;
    public Product $product;

    /**
     * Create a new notification instance.
     */
    public function __construct($transaction, $product)
    {
        $this->transaction = $transaction;
        $this->product = $product;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): Mailable
    {
        return (new UserPurchaseEmail($this->transaction, $this->product))
            ->to($notifiable->email);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
