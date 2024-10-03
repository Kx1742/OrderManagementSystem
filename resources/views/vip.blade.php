<!DOCTYPE html>
<html>
<head>
    <title>VIP Customer</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <a class="navbar-brand" href="#">McDonald Order System</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a class="nav-link" href="{{ url('/') }}">Home</a>
                </li>
            </ul>
        </div>
    </nav>

    <div class="container mt-5">
        <h1 class="mb-4">VIP Customer Orders</h1>
        
        <!-- Buttons -->
        <div class="d-flex justify-content-between mb-4">
            <form action="{{ url('/vip/order') }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-primary">Create VIP Order</button>
            </form>
            <a href="{{ url('/') }}" class="btn btn-secondary">Back to Welcome</a>
        </div>
       <!-- Bots Section -->
       <h2>Bots</h2>
        @if($bots->isEmpty())
            <p>No bots available.</p>
        @else
            <div class="card-columns">
                @foreach ($bots as $bot)
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Bot ID: {{ $bot->id }}</h5>
                            <p class="card-text"><strong>Status:</strong> <span class="badge badge-info">{{ $bot->status }}</span></p>
                            <p class="card-text"><strong>Processing Order ID:</strong> {{ $bot->order_id ?? 'None' }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
        <!-- Orders Section -->
        <div class="row mt-5">
            <div class="col-md-6">
                <h2>Pending Orders</h2>
                @if($pendingOrders->isEmpty())
                    <p>No pending orders.</p>
                @else
                    <div class="card-columns">
                        @foreach ($pendingOrders as $order)
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Order ID: {{ $order->id }}</h5>
                                    <p class="card-text"><strong>Type:</strong> {{ $order->type }}</p>
                                    <p class="card-text"><strong>Status:</strong> <span class="badge badge-warning">{{ $order->status }}</span></p>
                                    <p class="card-text"><small class="text-muted">Created at: {{ $order->created_at }}</small></p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
            <div class="col-md-6">
                <h2>Completed Orders</h2>
                @if($completedOrders->isEmpty())
                    <p>No completed orders.</p>
                @else
                    <div class="card-columns">
                        @foreach ($completedOrders as $order)
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Order ID: {{ $order->id }}</h5>
                                    <p class="card-text"><strong>Type:</strong> {{ $order->type }}</p>
                                    <p class="card-text"><strong>Status:</strong> <span class="badge badge-success">{{ $order->status }}</span></p>
                                    <p class="card-text"><small class="text-muted">Created at: {{ $order->created_at }}</small></p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <!-- Pagination Links -->
                    <div class="d-flex justify-content-center mt-4">
                        {{ $completedOrders->links('vendor.pagination.bootstrap-4') }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>