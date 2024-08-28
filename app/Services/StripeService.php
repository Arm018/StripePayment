<?php

namespace App\Services;
use Stripe\Webhook;
use App\Models\Order;
use App\Models\OrderPayment;
use App\Models\OrderProduct;
use App\Models\Product;
use Stripe\Checkout\Session;
use Stripe\Stripe;

class StripeService
{
    public function createSession(int $productId, int $quantity, Order $order, OrderProduct $orderProduct, OrderPayment $orderPayment): ?string
    {
        Stripe::setApiKey(config('stripe.sk'));

        $product = Product::query()->findOrFail($productId);

        $price = $product->price * 100;
        $totalAmount = ($price * $quantity) / 100;


        $order->fill([
            'total' => $totalAmount,
            'status' => Order::STATUS_PENDING,
        ])->save();

        $orderProduct->fill([
            'order_id' => $order->id,
            'product_id' => $productId,
            'quantity' => $quantity,
        ])->save();

        $orderPayment->fill([
           'order_id' => $order->id,
           'stripe_session_id' => '',
            'amount' => $totalAmount,
        ])->save();



        $lineItem = [
            'price_data' => [
                'currency' => 'USD',
                'product_data' => [
                    'name' => $product->name,
                ],
                'unit_amount' => $price,
            ],
            'quantity' => $quantity,
        ];

        $session = Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [$lineItem],
            'mode' => 'payment',
            'success_url' => route('success') . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('checkout'),
        ]);

        $orderPayment->update(['stripe_session_id' => $session->id]);

        return $session->url;
    }

    public function handleSuccess(string $sessionId): bool
    {
        $orderPayment = OrderPayment::query()->where('stripe_session_id', $sessionId)->first();

        if ($orderPayment) {
            $orderPayment->status = OrderPayment::STATUS_PAID;
            $orderPayment->save();
            $order = $orderPayment->order;
            if ($order) {
                $order->update(['status' => Order::STATUS_SUCCESS]);
            }
            return true;
        }

        return false;
    }


    public function handleWebhook(array $payload): bool
    {
        $sessionId = $payload['data']['object']['id'];
        $paymentStatus = $payload['data']['object']['payment_status'];
        $amountReceived = $payload['data']['object']['amount_total'];
        $currency = $payload['data']['object']['currency'];

        if ($sessionId) {
            $orderPayment = OrderPayment::where('stripe_session_id', $sessionId)->first();

            if ($orderPayment) {
                if ($paymentStatus === 'paid') {

                    $expectedAmount = $orderPayment->amount * 100;
                    $expectedCurrency = 'usd';

                    if ($amountReceived === $expectedAmount && $currency === $expectedCurrency) {
                        $orderPayment->update(['status' => OrderPayment::STATUS_PAID]);
                        $order = $orderPayment->order;
                        if ($order) {
                            $order->update(['status' => Order::STATUS_SUCCESS]);
                        }
                        return true;
                    } else {

                        return false;
                    }
                } else {

                    $orderPayment->update(['status' => OrderPayment::STATUS_FAILED]);
                    $order = $orderPayment->order;
                    if ($order) {
                        $order->update(['status' => Order::STATUS_FAILED]);
                    }
                    return true;
                }
            }
        }

        return false;
    }


}
