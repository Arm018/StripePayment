<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Web Shop</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }

        .container {
            margin-top: 50px;
        }

        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .card-header {
            background-color: #343a40;
            color: #fff;
            border-bottom: none;
            border-radius: 10px 10px 0 0;
            text-align: center;
            font-size: 1.5rem;
            padding: 1rem;
        }

        .card-body {
            padding: 2rem;
        }

        .quantity {
            width: 70px;
            text-align: center;
        }

        .btn-success {
            background-color: #28a745;
            border: none;
        }

        .btn-success:hover {
            background-color: #218838;
        }

        .table th, .table td {
            vertical-align: middle;
        }

        .img-responsive {
            border-radius: 5px;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    Pending Orders
                </div>
                <div class="card-body">
                    @if (session('message'))
                        <div class="alert alert-success">
                            {{ session('message') }}
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif
                    <a href="{{ route('checkout') }}" class="btn btn-primary mb-3">Back to Shop</a>

                </div>

            @foreach($orders as $order)
                    @if(isset($order) && $order->payments->count() > 0)
                        <h4 style="text-align: center">Pending Orders</h4>
                        <table class="table">
                            <thead>
                            <tr>
                                <th style="width:20%">Product ID</th>
                                <th style="width:40%">Payment Amount</th>
                                <th style="width:15%">Status</th>
                                <th style="width:25%">Action</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($order->payments as $payment)
                                @php
                                    $product = $order->products->first();
                                @endphp
                                <tr>
                                    <td>{{ $product->id }}</td>
                                    <td>${{ number_format($payment->amount, 2) }}</td>
                                    <td>{{ $payment->getStatusName() }}</td>
                                    <td>
                                        @if($payment->status === \App\Models\OrderPayment::STATUS_PENDING)
                                            <form action="{{ route('pay.existing.order') }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="order_payment_id"
                                                       value="{{ $payment->id }}">
                                                <input type="hidden" name="product_id" value="{{$product->id}}">
                                                <button type="submit" class="btn btn-success">Pay
                                                    ${{ number_format($payment->amount, 2) }} Now
                                                </button>
                                            </form>
                                        @else
                                            <button class="btn btn-secondary" disabled>Already Paid</button>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach

                            </tbody>
                        </table>
                    @endif
                @endforeach
            </div>
        </div>
    </div>
</div>
</body>
</html>
