<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class TestEvent implements ShouldBroadcast
{
    use SerializesModels;

    public $message;

    public function __construct($message)
    {
        $this->message = $message;
    }

    // Define the broadcast channel
    public function broadcastOn()
    {
        return new Channel('test-channel');
    }
    public function broadcastAs()
    {
        return 'test';
    }
    // Define the broadcast payload
    public function broadcastWith()
    {
        return [
            'message' => $this->message,
        ];
    }
}
