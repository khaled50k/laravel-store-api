<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class NewOrderPlaced implements ShouldBroadcast
{
    use SerializesModels;

    public $order;

    /**
     * Create a new event instance.
     *
     * @param array $order
     */
    public function __construct(array $order)
    {
        $this->order = $order;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn()
    {
        return new Channel('orders');
    }

    /**
     * Customize the event name.
     */
    public function broadcastAs()
    {
        return 'NewOrderPlaced';
    }

    /**
     * Data to broadcast.
     */
    public function broadcastWith()
    {
        // Ensure array access
        return [
            'order_id'   => data_get($this->order, 'order_id', 'Missing'),
            'order'      => data_get($this->order, 'order_number', 'Missing'),
            'customer'   => data_get($this->order, 'customer', 'Missing'),
            'email'      => data_get($this->order, 'email', 'Missing'),
            'total'      => data_get($this->order, 'total', 'Missing'),
            'status'     => data_get($this->order, 'status', 'Missing'),
            'currency'   => data_get($this->order, 'currency', 'Missing'),
            'message'    => data_get($this->order, 'message', 'A new order has been placed!'),
        ];
    }
}
