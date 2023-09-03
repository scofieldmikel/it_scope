<?php

namespace App\Notifications\Auth;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class EmailChangenotify extends Notification
{
    use Queueable;

    public User $user;
    
    private array $messages = [
        'title' => 'EMAIL VERIFIED',
        'msg' => 'Hello *NAME*, Your email has been successfully changed to *EMAIL*.',
    ];

    /**
     * Create a new notification instance.
     */
    public function __construct($user)
    {
        $this->user = $user;
        $this->replacePlaceHolder();
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->line('The introduction to the notification.')
                    ->action('Notification Action', url('/'))
                    ->line('Thank you for using our application!');
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
