<?php

namespace App\Http\Controllers;

use App\Http\Requests\OrderRequest;
use App\Models\Order;
use App\Models\OrderPayment;
use App\Models\OrderProduct;
use App\Models\Product;
use App\Services\StripeService;
use Illuminate\Http\Request;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;

class StripeController extends Controller
{
    protected $stripeService;

    public function __construct(StripeService $stripeService)
    {
        $this->stripeService = $stripeService;
    }

    public function checkout()
    {
        $products = Product::all();
        return view('checkout', compact('products'));
    }

    public function session(Request $request, Order $order, OrderProduct $orderProduct, OrderPayment $orderPayment)
    {
        try {
            $productId = $request->input('product_id');
            $quantity = $request->input('quantity', 1);

            $url = $this->stripeService->createSession($productId, $quantity, $order, $orderProduct, $orderPayment);

            return redirect()->away($url);
        } catch (\Exception $e) {
            return back()->with(['error' => $e->getMessage()]);
        }
    }

    public function success(Request $request)
    {
        $sessionId = $request->query('session_id');

        if ($this->stripeService->handleSuccess($sessionId)) {
            return back()->with(['message' => 'Thanks for your order. You have just completed your payment.']);
        }

        return back()->with(['error' => 'Order not found.']);
    }

    public function webhook(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $endpointSecret = config('stripe.webhook_secret');


        try {
            Webhook::constructEvent($payload, $sigHeader, $endpointSecret);
        } catch (\UnexpectedValueException $e) {
            return response()->json(['status' => 'error', 'message' => 'Invalid payload'], 400);
        } catch (SignatureVerificationException $e) {
            return response()->json(['status' => 'error', 'message' => 'Invalid signature'], 400);
        }

        $payload = json_decode($payload, true);

        if ($this->stripeService->handleWebhook($payload)) {
            return response()->json(['status' => 'success']);
        }

        return response()->json(['status' => 'error', 'message' => 'Order not found'], 404);
    }
}
