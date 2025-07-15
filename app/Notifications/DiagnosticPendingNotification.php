<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DiagnosticPendingNotification extends Notification
{
    use Queueable;

    protected $diagnostic;
    protected $deadline;
    protected $period;

    /**
     * Create a new notification instance.
     */
    public function __construct($diagnostic, $deadline, $period)
    {
        $this->diagnostic = $diagnostic;
        $this->deadline = $deadline;
        $this->period = $period;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return [\App\Channels\TenantDatabaseChannel::class];
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
            'tenant_id'     => $this->period->tenant_id,
            'diagnostic_id' => $this->diagnostic->id,
            'title'         => $this->diagnostic->title,
            'deadline'      => $this->deadline->toDateString(),
            'message'       => "Você ainda não respondeu o diagnóstico '{$this->diagnostic->title}'. Prazo: {$this->deadline->format('d/m/Y')}."
        ];
    }
}
