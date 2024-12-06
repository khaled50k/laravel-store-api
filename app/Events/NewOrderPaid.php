<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class NewOrderPaid implements ShouldBroadcast
{
    use SerializesModels;

    public $order;
    public $payment;

    /**
     * Create a new event instance.
     *
     * @param array $order
     * @param object $payment
     */
    public function __construct(array $order, $payment)
    {
        $this->order = $order;    // Ensure it's an array
        $this->payment = $payment;  // Eloquent model
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
        return 'NewOrderPaid';
    }

    /**
     * The data to broadcast.
     */
    public function broadcastWith()
    {
        // Ensure safe data access
        return [
            'order_id'        => data_get($this->order, 'order_id', 'Missing'),
            'order_number'    => data_get($this->order, 'order_number', 'Missing'),
            'customer'        => data_get($this->order, 'customer', 'Missing'),
            'email'           => data_get($this->order, 'email', 'Missing'),
            'total'           => data_get($this->order, 'total', 'Missing'),
            'status'          => data_get($this->order, 'status', 'Missing'),
            'currency'        => data_get($this->order, 'currency', 'Missing'),
            'transaction_id'  => data_get($this->payment, 'transaction_id', 'Missing'),
            'amount'          => data_get($this->payment, 'amount', 'Missing'),
            'payment_status'  => data_get($this->payment, 'status', 'Missing'),
            'message'         => data_get($this->order, 'message', 'Order Paid Successfully!'),
        ];
    }
}
