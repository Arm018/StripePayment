<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderPayment;
use App\Models\Product;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function createOrder(Request $request)
    {
        $productIds = $request->input('product_ids', []);
        $quantities = $request->input('quantities', []);
        $totalAmount = 0;

        $order = new Order();
        $order->total = $totalAmount;
        $order->status = Order::STATUS_PENDING;
        $order->save();

        foreach ($productIds as $productId) {
            $product = Product::findOrFail($productId);
            $quantity = $quantities[$productId];
            $price = $product->price * $quantity;
            $totalAmount += $price;

            $order->products()->attach($productId, ['quantity' => $quantity]);
        }

        $order->total = $totalAmount;
        $order->save();

        return redirect()->route('order.page', ['order_id' => $order->id, 'total_amount' => $totalAmount]);
    }

    public function showOrderPage($orderId, $totalAmount)
    {

        return view('order', compact('orderId', 'totalAmount'));
    }

    public function splitOrder(Request $request)
    {
        $orderId = $request->input('order_id');
        $splits = $request->input('splits', []);

        $order = Order::findOrFail($orderId);
        $totalAmount = array_sum($splits);

        if ($totalAmount != $order->total) {
            return redirect()->back()->with('error', 'Total amount does not match the sum of split amounts.');
        }

        $minSplitValue = 500;
        foreach ($splits as $split) {
            if ($split < $minSplitValue) {
                return redirect()->back()->with('error', 'Each split must be at least 500$ of the total amount.');
            }
        }

        foreach ($splits as $split) {
            OrderPayment::create([
                'order_id' => $order->id,
                'amount' => $split,
                'status' => OrderPayment::STATUS_PENDING,
                'stripe_session_id' => '',
            ]);
        }

        return redirect()->route('orders.pending')->with('message', 'Order and payments created successfully.');
    }

}
