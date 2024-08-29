<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderPayment;
use App\Models\OrderProduct;
use App\Models\Product;
use Stripe\Checkout\Session;
use Stripe\Stripe;

class StripeService
{
    public function createSession(array $productIds, array $quantities, Order $order): ?string
    {
        Stripe::setApiKey(config('stripe.sk'));

        $totalAmount = 0;
        $lineItems = [];


        foreach ($productIds as $productId) {
            $product = Product::findOrFail($productId);
            $quantity = $quantities[$productId];
            $price = $product->price * 100;
            $totalAmount += $price * $quantity;

            $lineItems[] = [
                'price_data' => [
                    'currency' => 'USD',
                    'product_data' => [
                        'name' => $product->name,
                    ],
                    'unit_amount' => $price,
                ],
                'quantity' => $quantity,
            ];

            $order->fill([
                'total' => $totalAmount / 100,
                'status' => Order::STATUS_PENDING,
            ])->save();


            $orderProduct = new OrderProduct();
            $orderProduct->fill([
                'order_id' => $order->id,
                'product_id' => $productId,
                'quantity' => $quantity,
            ])->save();

        }

        $splitAmount = 1000 * 100;
        $remainingAmount = $totalAmount;
        $splitAmounts = [];

        while ($remainingAmount > 0) {
            $currentAmount = min($splitAmount, $remainingAmount);
            $splitAmounts[] = $currentAmount;
            $remainingAmount -= $currentAmount;
        }


        foreach ($splitAmounts as $amount) {
            $orderPayment = new OrderPayment();
            $orderPayment->fill([
                'order_id' => $order->id,
                'stripe_session_id' => '',
                'amount' => $amount / 100,
                'status' => OrderPayment::STATUS_PENDING,
            ])->save();
        }

        $session = Session::create([
            'payment_method_types' => ['card'],
            'line_items' => $lineItems,
            'mode' => 'payment',
            'success_url' => route('success') . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('checkout'),
        ]);

        foreach ($order->payments as $orderPayment) {
            $orderPayment->update(['stripe_session_id' => $session->id]);
        }

        return $session->url;
    }


    public function handleSuccess(string $sessionId): bool
    {
        $orderPayments = OrderPayment::query()->where('stripe_session_id', $sessionId)->get();

        foreach ($orderPayments as $orderPayment) {
            if ($orderPayment) {
                $orderPayment->update(['status' => OrderPayment::STATUS_PAID]);

                $order = $orderPayment->order;
                if ($order) {
                    $allPaid = $order->payments()->where('status', OrderPayment::STATUS_PAID)->count() === $order->payments()->count();

                    $order->update(['status' => $allPaid ? Order::STATUS_SUCCESS : Order::STATUS_PENDING]);
                }
            }
        }
        return true;
    }


    public function handleWebhook(array $payload): bool
    {
        $sessionId = $payload['data']['object']['id'] ?? null;
        $paymentStatus = $payload['data']['object']['payment_status'] ?? 'unknown';
        $amountReceived = $payload['data']['object']['amount_total'] / 100;
        $currency = $payload['data']['object']['currency'] ?? 'usd';

        if ($sessionId) {
            $orderPayments = OrderPayment::query()->where('stripe_session_id', $sessionId)->get();

            foreach ($orderPayments as $orderPayment) {
                if ($orderPayment) {
                    $expectedAmount = $orderPayment->amount;
                    $expectedCurrency = 'usd';

                    if ($paymentStatus === 'paid') {
                        if ($amountReceived == $expectedAmount && $currency === $expectedCurrency) {
                            $orderPayment->update(['status' => OrderPayment::STATUS_PAID]);
                        } else {
                            $orderPayment->update(['status' => OrderPayment::STATUS_FAILED]);
                        }
                    } else {
                        $orderPayment->update(['status' => OrderPayment::STATUS_FAILED]);
                    }

                    $order = $orderPayment->order;
                    if ($order) {
                        $order->updateStatus();
                    }

                    return true;
                }
            }
        }

        return false;
    }

    public function createSessionForExistingPayment(OrderPayment $orderPayment, int $productId): ?string
    {
        Stripe::setApiKey(config('stripe.sk'));

        $product = Product::findOrFail($productId);
        $amount = $orderPayment->amount * 100;

        $lineItem = [
            'price_data' => [
                'currency' => 'USD',
                'product_data' => [
                    'name' => $product->name,
                ],
                'unit_amount' => $amount,
            ],
            'quantity' => 1,
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


}
