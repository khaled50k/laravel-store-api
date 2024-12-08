<?php

namespace App\Http\Controllers\API;

use App\Events\NewOrderPaid;
use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use PayPalCheckoutSdk\Core\ProductionEnvironment;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;
use PayPalCheckoutSdk\Orders\OrdersCaptureRequest;
use App\Models\Order;
use App\Models\Payments;

class PayPalController extends BaseController
{
    protected $client;

    public function __construct()
    {
        $environment = env('PAYPAL_MODE') === 'live'
            ? new ProductionEnvironment(env('PAYPAL_LIVE_CLIENT_ID'), env('PAYPAL_LIVE_CLIENT_SECRET'))
            : new SandboxEnvironment(env('PAYPAL_SANDBOX_CLIENT_ID'), env('PAYPAL_SANDBOX_CLIENT_SECRET'));

        $this->client = new PayPalHttpClient($environment);
    }


    /**
     * Create PayPal Payment
     */
    public function createPayment(Request $request)
    {
        $id = $request->query('id');
        $order = Order::findOrFail($id);

        $paypalOrderRequest = new OrdersCreateRequest();
        $paypalOrderRequest->prefer('return=representation');
        $paypalOrderRequest->body = [
            "intent" => "CAPTURE",
            "purchase_units" => [
                [
                    "amount" => [
                        "currency_code" => "USD",
                        "value" => $order->total,
                    ],
                    "custom_id" => (string) $order->id,   // Attach custom ID securely
                    "invoice_id" => (string) $order->order_number, // Optional
                ],
            ],
            "application_context" => [
                "cancel_url" => route('paypal.cancel'),
                "return_url" => env('FRONTEND_URL') . '/paypal/success',
            ],
        ];

        try {
            $response = $this->client->execute($paypalOrderRequest);

            return $this->sendResponse([
                'paypal_order_id' => $response->result->id,
                'approval_url' => collect($response->result->links)
                    ->firstWhere('rel', 'approve')->href,
            ], 'PayPal payment created successfully.');
        } catch (\Exception $e) {
            return $this->sendError('PayPal API Error.', ['error' => $e->getMessage()]);
        }
    }



    /**
     * Capture PayPal Payment
     */
    public function capturePayment(Request $request)
    {
        $poid = $request->query('poid');  // PayPal Order ID
        $captureRequest = new OrdersCaptureRequest($poid);
    
        try {
            $response = $this->client->execute($captureRequest);
    
            // Log entire PayPal capture response
    
            if ($response->result->status === 'COMPLETED') {
                // Extract Application Order ID securely
                $customId = data_get($response->result, 'purchase_units.0.payments.captures.0.custom_id');
    
                if (!$customId) {
                    return $this->sendError('Order ID not found in PayPal response.');
                }
    
                // Find and update the order
                $order = Order::find($customId);
                if (!$order) {
                    return $this->sendError('Order not found.');
                }
    
                $order->status = 'paid';
                $order->save();
    
                // Format the order payload
                $formattedOrder = [
                    'order_id'    => (string) $order->id, 
                    'order_number'=> (string) $order->order_number, 
                    'customer'    => $order->user->first_name . ' ' . $order->user->last_name,
                    'email'       => $order->user->email,
                    'total'       => number_format($order->total, 2, '.', ''),
                    'status'      => ucfirst($order->status),
                    'currency'    => strtoupper($order->currency),
                    'message'     => 'The order has been paid successfully!',
                ];
    
                // Record the payment
                $payment = Payments::create([
                    'order_id'        => $customId,
                    'payment_gateway' => 'PayPal',
                    'transaction_id'  => $response->result->id,
                    'amount'          => $response->result->purchase_units[0]->payments->captures[0]->amount->value,
                    'status'          => 'completed',
                ]);
    
                // Trigger the event
                broadcast(new NewOrderPaid($formattedOrder, $payment));
    
                return $this->sendResponse($response->result, 'Payment captured successfully.');
            }
    
            return $this->sendError('Payment not completed.', $response->result);
        } catch (\Exception $e) {
            return $this->sendError('PayPal API Error.', ['error' => $e->getMessage()]);
        }
    }
    



    /**
     * Cancel Payment
     */
    public function cancelPayment()
    {
        return $this->sendError('Payment was canceled by the user.');
    }

    /**
     * Success Payment
     */
    public function successPayment()
    {
        return $this->sendResponse([], 'Payment was successful.');
    }
}
