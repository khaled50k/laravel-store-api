<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class NewUserRegistered implements ShouldBroadcast
{
    use InteractsWithSockets, SerializesModels;

    public $user;

    /**
     * Create a new event instance.
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn()
    {
        return new Channel('admin-notifications');
    }
    public function broadcastAs()
    {
        return 'NewUserRegistered';
    }
    /**
     * The data to broadcast.
     */
    public function broadcastWith()
    {
        return [
            'id' => $this->user->id,
            'name' => $this->user->first_name . ' ' . $this->user->last_name,
            'email' => $this->user->email,
            'phone' => $this->user->phone,
            'message' => 'A new user has registered.',
        ];
    }
}
