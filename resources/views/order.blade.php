<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Order Page</title>
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

        .btn-group {
            display: flex;
            gap: 10px;
        }

        .btn-primary {
            width: 100%;
        }

        #splitForm {
            display: none; /* Hide initially */
        }

        #error-message {
            color: red;
            display: none; /* Hide initially */
        }
    </style>
</head>
<body>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    Order Page
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

                    <h4>Total Amount: ${{ number_format($totalAmount, 2) }}</h4>

                    <h5>Products</h5>
                    <table class="table">
                        <thead>
                        <tr>
                            <th>Name</th>
                            <th>Quantity</th>
                            <th>Price</th>
                            <th>Total</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($order->products as $product)
                            <tr>
                                <td>{{ $product->name }}</td>
                                <td>
                                    <input type="number" name="quantities[{{ $product->id }}]" value="{{ $product->pivot->quantity }}" min="1" class="form-control quantity" form="payNowForm">
                                </td>
                                <td>${{ number_format($product->price, 2) }}</td>
                                <td>${{ number_format($product->price * $product->pivot->quantity, 2) }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>

                    <div class="btn-group">
                        <!-- Pay Now Form -->
                        <form id="payNowForm" action="{{ route('session') }}" method="POST">
                            @csrf
                            @foreach ($order->products as $product)
                                <input type="hidden" name="product_ids[]" value="{{ $product->id }}">
                            @endforeach
                            <button type="submit" class="btn btn-primary">Pay Now</button>
                        </form>

                        <!-- Split Payment Button -->
                        <button type="button" id="addSplitButton" class="btn btn-success">Split Payment</button>
                    </div>

                    <!-- Split Payment Form -->
                    <form id="splitForm" action="{{ route('order.split') }}" method="POST" class="mt-3">
                        @csrf
                        <input type="hidden" name="order_id" value="{{ $orderId }}">
                        <div id="splitInputs">
                            <!-- Split inputs will be added here dynamically -->
                        </div>
                        <div id="error-message">Your splits do not match the total amount.</div>
                        <button type="submit" class="btn btn-success mt-3">Submit Splits</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const totalAmount = {{ $totalAmount }};
        const splitForm = document.getElementById('splitForm');
        const splitInputsContainer = document.getElementById('splitInputs');
        const errorMessage = document.getElementById('error-message');
        let remainingAmount = totalAmount;

        function createSplitInput(amount = '') {
            const splitRow = document.createElement('div');
            splitRow.className = 'input-group mb-2';

            const input = document.createElement('input');
            input.type = 'number';
            input.step = '0.01';
            input.min = '0.01';
            input.className = 'form-control';
            input.name = 'splits[]';
            input.placeholder = 'Enter split amount';
            input.value = amount;
            input.required = true;

            const addButton = document.createElement('button');
            addButton.type = 'button';
            addButton.className = 'btn btn-outline-secondary';
            addButton.textContent = '+';
            addButton.onclick = function () {
                const sumOfSplits = Array.from(splitInputsContainer.querySelectorAll('input'))
                    .reduce((sum, input) => sum + parseFloat(input.value || 0), 0);

                if (sumOfSplits < totalAmount) {
                    createSplitInput('');
                }
            };

            const removeButton = document.createElement('button');
            removeButton.type = 'button';
            removeButton.className = 'btn btn-outline-danger';
            removeButton.textContent = '-';
            removeButton.onclick = function () {
                splitRow.remove();
                handleSplitAmount();
            };

            splitRow.appendChild(input);
            splitRow.appendChild(addButton);
            splitRow.appendChild(removeButton);
            splitInputsContainer.appendChild(splitRow);

            input.addEventListener('change', handleSplitAmount);
        }

        function handleSplitAmount() {
            const inputs = Array.from(splitInputsContainer.querySelectorAll('input'));
            const sumOfSplits = inputs.reduce((sum, input) => sum + parseFloat(input.value || 0), 0);

            remainingAmount = totalAmount - sumOfSplits;

            inputs.forEach(input => {
                input.max = (remainingAmount + parseFloat(input.value)).toFixed(2);
            });

            if (remainingAmount <= 0) {
                splitForm.querySelector('button[type="submit"]').disabled = false;
                errorMessage.style.display = 'none';
            } else {
                splitForm.querySelector('button[type="submit"]').disabled = true;
                errorMessage.style.display = 'block';
            }
        }

        document.getElementById('addSplitButton').addEventListener('click', function () {
            splitForm.style.display = 'block'; // Show the form
            createSplitInput();
        });

        createSplitInput();
    });
</script>
</body>
</html>
