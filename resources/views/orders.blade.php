<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>FeedMe Assignment</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Nunito', sans-serif; }
        .container { max-width: 800px; margin: 0 auto; padding: 20px; }
        .order { padding: 10px; border: 1px solid #ccc; margin-bottom: 10px; }
        .bot { padding: 10px; border: 1px solid #ccc; margin-bottom: 10px; }
    </style>
    <script>
        async function addOrder(type) {
            const response = await fetch(`/add-${type}-order`);
            const data = await response.json();
            updateOrders(data.pendingOrders, data.completedOrders, data.bots);
        }

        async function addBot() {
            const response = await fetch('/add-bot');
            const data = await response.json();
            updateOrders(data.pendingOrders, data.completedOrders, data.bots);
        }

        async function removeBot() {
            const response = await fetch('/remove-bot');
            const data = await response.json();
            updateOrders(data.pendingOrders, data.completedOrders, data.bots);
        }

        function updateOrders(pendingOrders, completedOrders, bots) {
            const pendingOrdersContainer = document.getElementById('pending-orders');
            const completedOrdersContainer = document.getElementById('completed-orders');
            const botsContainer = document.getElementById('bots');

            pendingOrdersContainer.innerHTML = '';
            completedOrdersContainer.innerHTML = '';
            botsContainer.innerHTML = '';

            pendingOrders.forEach(order => {
                const orderElement = document.createElement('div');
                orderElement.className = 'order';
                orderElement.textContent = `${order.id} - ${order.type}`;
                pendingOrdersContainer.appendChild(orderElement);
            });

            completedOrders.forEach(order => {
                const orderElement = document.createElement('div');
                orderElement.className = 'order';
                orderElement.textContent = `${order.id} - ${order.type}`;
                completedOrdersContainer.appendChild(orderElement);
            });

            bots.forEach(bot => {
                const botElement = document.createElement('div');
                botElement.className = 'bot';
                botElement.textContent = `${bot.id} - ${bot.status}`;
                botsContainer.appendChild(botElement);
            });
        }

        // Fetch initial data and update the UI
        async function fetchInitialData() {
            const response = await fetch('/');
            const data = await response.json();
            updateOrders(data.pendingOrders, data.completedOrders, data.bots);
        }

        // Fetch initial data on page load
        window.onload = fetchInitialData;
    </script>
</head>
<body>
    <div class="container">
        <h1>FeedMe Assignment</h1>
        <button onclick="addOrder('normal')">New Normal Order</button>
        <button onclick="addOrder('vip')">New VIP Order</button>
        <button onclick="addBot()">+ Bot</button>
        <button onclick="removeBot()">- Bot</button>

        <h2>Pending Orders</h2>
        <div id="pending-orders">
            @foreach ($pendingOrders as $order)
                <div class="order">{{ $order->id }} - {{ $order->type }}</div>
            @endforeach
        </div>

        <h2>Completed Orders</h2>
        <div id="completed-orders">
            @foreach ($completedOrders as $order)
                <div class="order">{{ $order->id }} - {{ $order->type }}</div>
            @endforeach
        </div>

        <h2>Bots</h2>
        <div id="bots">
            @foreach ($bots as $bot)
                <div class="bot">{{ $bot->id }} - {{ $bot->status }}</div>
            @endforeach
        </div>
    </div>
</body>
</html>