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

        $splitAmount = 1000;
        $remainingAmount = $totalAmount;
        $splitAmounts = [];

        while ($remainingAmount > 0) {
            $currentAmount = min($splitAmount, $remainingAmount);
            $splitAmounts[] = $currentAmount;
            $remainingAmount -= $currentAmount;
        }

        foreach ($splitAmounts as $amount) {
            OrderPayment::create([
                'order_id' => $order->id,
                'stripe_session_id' => '',
                'amount' => $amount,
                'status' => OrderPayment::STATUS_PENDING,
            ]);
        }



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

        foreach (OrderPayment::where('order_id', $order->id)->get() as $orderPayment) {
            $orderPayment->update(['stripe_session_id' => $session->id]);
        }

        return $session->url;
    }

    public function handleSuccess(string $sessionId): bool
    {
        $orderPayments = OrderPayment::where('stripe_session_id', $sessionId)->get();

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
            $orderPayments = OrderPayment::where('stripe_session_id', $sessionId)->get();

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




}
