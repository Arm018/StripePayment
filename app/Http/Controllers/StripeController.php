<?php

namespace App\Http\Controllers;


use App\Models\Order;
use App\Models\OrderPayment;
use App\Models\Product;
use App\Services\StripeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
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

    public function pending()
    {

        $orders = Order::whereHas('payments', function ($query) {
            $query->where('status', OrderPayment::STATUS_PENDING);
        })->with('payments')->with('products')->get();

        return view('pending', compact('orders'));

    }


    public function session(Request $request, Order $order): RedirectResponse
    {

        $productIds = $request->input('product_ids', []);
        $quantities = $request->input('quantities', []);

        if (!$productIds) {
            return back()->with(['error' => 'Please select at least one product and quantity.']);
        }

        $url = $this->stripeService->createSession($productIds, $quantities, $order);

        return redirect()->away($url);

    }


    public function success(Request $request): RedirectResponse
    {
        $sessionId = $request->query('session_id');

        if ($this->stripeService->handleSuccess($sessionId)) {
            return back()->with(['message' => 'Thanks for your order. You have just completed your payment.']);
        }

        return back()->with(['error' => 'Order not found.']);
    }

    public function webhook(Request $request): JsonResponse
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

    public function payExistingOrder(Request $request): RedirectResponse
    {
        $orderPaymentId = $request->input('order_payment_id');
        $productId = $request->input('product_id');

        $orderPayment = OrderPayment::findOrFail($orderPaymentId);


        $url = $this->stripeService->createSessionForExistingPayment($orderPayment, $productId);

        return redirect()->away($url);
    }


}
