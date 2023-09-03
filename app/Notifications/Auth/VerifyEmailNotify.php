<?php

namespace App\Notifications\Auth;

use App\Mail\VerifyEmail;
use App\Models\User;
use App\Services\TotpService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class VerifyEmailNotify extends Notification implements ShouldQueue
{
    use Queueable;

    public User $user;
    public TotpService $totpService;
    
    private array $messages = [
        'title' => 'EMAIL TOKEN SENT',
        'msg' => 'Hello *NAME* Your one time token has been sent to *EMAIL* Please copy it and complete your action.',
    ];
    /**
     * Create a new notification instance.
     */
    public function __construct($user, $totpService)
    {
        $this->user = $user;
        $this->totpService = $totpService;
        $this->replacePlaceHolder();
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): Mailable
    {
        return (new VerifyEmail($this->user, $this->totpService))
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
            'notification' => [
                'title' => $this->messages['title'],
                'body' => $this->messages['msg'],
            ],
        ];
    }

    protected function replacePlaceHolder(): void
    {
        $data = [
            '*NAME*' => ucwords($this->user->name),
            '*EMAIL*' => $this->user->email,
        ];
        $this->messages = array_map(function ($message) use ($data) {
            return str_replace(array_keys($data), array_values($data), $message);
        }, $this->messages);
    }
}
