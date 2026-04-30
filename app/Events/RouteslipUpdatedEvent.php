<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RouteslipUpdatedEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $routeslip;
    public $userId;

    public function __construct($routeslip, $userId) {
        $this->routeslip = $routeslip;
        $this->userId = $userId;
    }

    public function broadcastOn() {
        // Private channel para security - user lang na iyon ang makakakita
        return new PrivateChannel('user.'.$this->userId);
    }

    public function broadcastAs() {
        return 'routeslip.updated';
    }

    public function broadcastWith() {
        return [
            'id'           => $this->routeslip->id,
            'routeslip_no' => $this->routeslip->routeslip_no,
            'r_subject'    => $this->routeslip->r_subject,
            'urgency'      => $this->routeslip->urgency ?? 'Normal',
        ];
    }
}
