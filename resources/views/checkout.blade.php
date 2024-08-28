<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laravel 10 Stripe Payment Integration</title>
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
                    Laravel 10 Stripe Payment Integration
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

                    <table id="cart" class="table table-hover">
                        <thead>
                        <tr>
                            <th style="width:50%">Product</th>
                            <th style="width:10%">Price</th>
                            <th style="width: 18%">Quantity</th>
                            <th style="width:10%"></th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($products as $product)
                            <tr>
                                <td data-th="Product">
                                    <div class="row">
                                        <div class="col-sm-3">
                                            <img src="/asus.png" class="img-responsive" width="100" height="100">
                                        </div>
                                        <div class="col-sm-9">
                                            <h4 class="nomargin" style="text-align: center; margin-top: 25px">{{ $product->name }}</h4>
                                        </div>
                                    </div>
                                </td>
                                <td data-th="Price">${{ number_format($product->price, 2) }}</td>
                                <td data-th="Quantity">
                                    <form action="{{ route('session') }}" method="POST" class="form-inline">
                                        @csrf
                                        <input type="number" name="quantity" id="quantity_{{ $product->id }}" value="1" min="1" class="form-control quantity">

                                </td>
                                <td class="actions">
                                    <input type='hidden' name="product_id" value="{{ $product->id }}">
                                    <input type='hidden' name="total" value="{{ $product->price }}">
                                    <button class="btn btn-success btn-sm" type="submit"><i class="fa fa-money"></i> Checkout</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
