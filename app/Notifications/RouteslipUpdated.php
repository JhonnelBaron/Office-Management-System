<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RouteslipUpdated extends Notification
{
    use Queueable;
    protected $routeslip;

    /**
     * Tinatanggap natin yung $routeslip data mula sa Controller
     */
    public function __construct($routeslip)
    {
        $this->routeslip = $routeslip;
    }

    /**
     * Palitan natin ang 'mail' ng 'database' para sa system lang siya papasok
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Ito ang mahalaga: Dito natin ise-set kung anong data ang mase-save sa table
     */
    public function toArray(object $notifiable): array
    {
        return [
            'id'            => $this->routeslip->id,
            'routeslip_no' => $this->routeslip->routeslip_no,
            'subject'      => $this->routeslip->r_subject,
            'status'       => $this->routeslip->r_action_taken,
            'message'      => "New update on Routeslip #" . $this->routeslip->routeslip_no,
            'link'         => "/routeslips/" . $this->routeslip->id, // Optional link
        ];
    }
}
