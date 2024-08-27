<?php

namespace App\Http\Controllers;

use App\Models\OrderProduct;
use App\Models\Product;
use Illuminate\Http\Request;
use Stripe\Checkout\Session;
use Stripe\Stripe;
use App\Models\Order;

class StripeController extends Controller
{
    public function checkout()
    {
        $products = Product::all();
        return view('checkout', compact('products'));
    }

    public function session(Request $request)
    {
        Stripe::setApiKey(config('stripe.sk'));

        $productIds = $request->input('product_ids');
        $totalPrice = $request->input('total');


        if (is_array($productIds) && count($productIds) > 0) {

            $products = Product::whereIn('id', $productIds)->get();
        } else {
            // Handle single product
            $productId = $request->input('product_id');
            $product = Product::find($productId);
            $products = collect([$product]);
        }

        $lineItems = $products->map(function ($product) {
            return [
                'price_data' => [
                    'currency'     => 'USD',
                    'product_data' => [
                        'name' => $product->name,
                    ],
                    'unit_amount'  => $product->price * 100,
                ],
                'quantity'   => 1,
            ];
        })->toArray();

        $session = Session::create([
            'payment_method_types' => ['card'],
            'line_items' => $lineItems,
            'mode'        => 'payment',
            'success_url' => route('success').'?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url'  => route('checkout'),
        ]);


        $order = Order::create([
            'total' => $totalPrice,
            'status' => Order::STATUS_PENDING,
            'stripe_session_id' => $session->id,
        ]);


        foreach ($products as $product) {
            OrderProduct::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'quantity' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return redirect()->away($session->url);
    }






    public function success(Request $request)
    {
        $sessionId = $request->query('session_id');
        $order = Order::where('stripe_session_id', $sessionId)->first();

        if ($order) {
            $order->update(['status' => Order::STATUS_SUCCESS]);

            return back()->with(['message' => 'Thanks for your order. You have just completed your payment.']);
        }

        return back()->with(['error' => 'Order not found.']);
    }

    public function webhook(Request $request)
    {
        $payload = $request->all();
        $sessionId = $payload['data']['object']['id'] ?? null;

        if ($sessionId) {
            $order = Order::where('stripe_session_id', $sessionId)->first();

            if ($order) {
                $order->update(['status' => Order::STATUS_SUCCESS]);
            } else {
                return response()->json(['status' => 'error', 'message' => 'Order not found'], 404);
            }
        }

        return response()->json(['status' => 'success']);
    }
}
