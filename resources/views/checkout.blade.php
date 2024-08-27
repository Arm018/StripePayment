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
        .nomargin {
            margin: 0;
            font-weight: bold;
        }
        .quantity {
            width: 60px;
            text-align: center;
        }
        .btn-success {
            background-color: #28a745;
            border: none;
        }
        .btn-success:hover {
            background-color: #218838;
        }
        .btn-danger {
            background-color: #dc3545;
            border: none;
        }
        .btn-danger:hover {
            background-color: #c82333;
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
                    <table id="cart" class="table table-hover">
                        <thead>
                        <tr>
                            <th style="width:50%">Product</th>
                            <th style="width:10%">Price</th>
                            <th style="width:8%">Quantity</th>
                            <th style="width:22%" class="text-center">Subtotal</th>
                            <th style="width:10%"></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td data-th="Product">
                                <div class="row">
                                    <div class="col-sm-3"><img src="/asus.png" class="img-responsive" width="100" height="100" alt="Product Image"></div>
                                    <div class="col-sm-9">
                                        <h4 class="" style="text-align: center;margin-top: 25px">Asus Vivobook </h4>
                                    </div>
                                </div>
                            </td>
                            <td data-th="Price">$6</td>
                            <td data-th="Quantity">
                                <input type="number" value="1" class="form-control quantity cart_update" min="1">
                            </td>
                            <td data-th="Subtotal" class="text-center">$6</td>
                            <td class="actions">
                                <button class="btn btn-danger btn-sm cart_remove"><i class="fa fa-trash-o"></i> Delete</button>
                            </td>
                        </tr>
                        </tbody>
                        <tfoot>
                        <tr>
                            <td colspan="5" class="text-end"><h3><strong>Total $6</strong></h3></td>
                        </tr>
                        <tr>
                            <td colspan="5" class="text-end">
                                <form action="/session" method="POST">
                                    <a href="{{ url('/') }}" class="btn btn-danger"><i class="fa fa-arrow-left"></i> Continue Shopping</a>
                                    @csrf
                                    <input type='hidden' name="total" value="6">
                                    <input type='hidden' name="productName" value="Asus Vivobook">
                                    <button class="btn btn-success" type="submit" id="checkout-live-button"><i class="fa fa-money"></i> Checkout</button>
                                </form>
                            </td>
                        </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
