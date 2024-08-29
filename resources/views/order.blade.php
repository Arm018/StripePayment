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

                    <form id="splitForm" action="{{ route('order.split') }}" method="POST">
                        @csrf
                        <input type="hidden" name="order_id" value="{{ $orderId }}">
                        <div id="splitInputs">

                        </div>
                        <button type="submit" class="btn btn-success">Submit Splits</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const totalAmount = {{ $totalAmount }};
        const splitForm = document.getElementById('splitForm');
        const splitInputsContainer = document.getElementById('splitInputs');

        function createSplitInput(amount) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'splits[]';
            input.value = amount;
            splitInputsContainer.appendChild(input);
        }


        function handleSplitAmount() {
            const splitAmountInput = document.getElementById('splitAmount');
            const splitAmount = parseFloat(splitAmountInput.value);

            if (!isNaN(splitAmount) && splitAmount > 0 && splitAmount <= totalAmount) {
                splitInputsContainer.innerHTML = '';
                createSplitInput(splitAmount);

                if (splitAmount < totalAmount) {
                    createSplitInput(totalAmount - splitAmount);
                }
            }
        }


        const splitAmountInput = document.createElement('input');
        splitAmountInput.type = 'number';
        splitAmountInput.id = 'splitAmount';
        splitAmountInput.placeholder = 'Enter split amount';
        splitAmountInput.className = 'form-control mb-3';
        splitInputsContainer.appendChild(splitAmountInput);

        splitAmountInput.addEventListener('change', handleSplitAmount);
    });
</script>
</body>
</html>
