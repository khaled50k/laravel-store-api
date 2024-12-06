<?php

namespace App\Http\Controllers\API;

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
            : new SandboxEnvironment(env('PAYPAL_SANDBOX_CLIENT_ID'),env('PAYPAL_SANDBOX_CLIENT_SECRET'));
    
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
                ],
            ],
            "application_context" => [
                "cancel_url" => route('paypal.cancel'),
                "return_url" => route('paypal.success'),
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
        $id = $request->query('poid');
        $oid = $request->query('oid');

        $captureRequest = new OrdersCaptureRequest($id);

        try {
            $response = $this->client->execute($captureRequest);

            if ($response->result->status === 'COMPLETED') {
                if ($oid) {
                    Payments::create([
                        'order_id' => $oid,
                        'payment_gateway' => 'PayPal',
                        'transaction_id' => $response->result->id,
                        'amount' => $response->result->purchase_units[0]->payments->captures[0]->amount->value,
                        'status' => 'completed',
                    ]);
                } else {
                    return $this->sendError('PayPal API Error.', ['error' => 'Order ID is null.']);
                }

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
