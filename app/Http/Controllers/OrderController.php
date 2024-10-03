<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Bot;
use App\Jobs\ProcessOrderJob;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    public function index()
    {
        return view('orders', [
            'pendingOrders' => Order::where('status', 'pending')->orderBy('created_at', 'desc')->get(),
            'completedOrders' => Order::where('status', 'completed')->orderBy('created_at', 'desc')->paginate(10), // 10 orders per page
            'bots' => Bot::all(),
        ]);
    }


    public function showCustomerView()
    {
        $this->processOrders(); // Process any pending orders when the manager view is accessed
        return view('customer', [
            'pendingOrders' => Order::where('status', 'pending')->orderBy('created_at', 'desc')->get(),
            'completedOrders' => Order::where('status', 'completed')->orderBy('created_at', 'desc')->paginate(10), // 10 orders per page
            'bots' => Bot::all(),
        ]);
    }

    public function showVipView()
    {
        $this->processOrders(); // Process any pending orders when the manager view is accessed
        return view('vip', [
            'pendingOrders' => Order::where('status', 'pending')
                                    ->orderByRaw("FIELD(type, 'VIP', 'Normal')")
                                    ->orderBy('created_at', 'desc')
                                    ->get(),
            'completedOrders' => Order::where('status', 'completed')->orderBy('created_at', 'desc')->paginate(10), // 10 orders per page
            'bots' => Bot::all(),
        ]);
    }

    public function showManagerView()
    {
        $this->processOrders(); // Process any pending orders when the manager view is accessed
        return view('manager', [
            'pendingOrders' => Order::where('status', 'pending')->orderBy('created_at', 'desc')->get(),
            'completedOrders' => Order::where('status', 'completed')->orderBy('created_at', 'desc')->paginate(10), // 10 orders per page
            'bots' => Bot::all(),
        ]);
    }

    public function addNormalOrder()
    {
        $order = Order::create(['type' => 'normal']);
        ProcessOrderJob::dispatch($order);

        return redirect('/customer');
    }

    public function addVipOrder()
    {
        $order = Order::create(['type' => 'vip']);
        ProcessOrderJob::dispatch($order);

        return redirect('/vip');
    }

    public function addBot()
    {
        $bot = Bot::create(['status' => 'idle']);
        $this->processOrders(); // Immediately process any pending orders

        return redirect('/manager');
    }

    public function removeBot()
    {
        $newestBot = Bot::orderBy('created_at', 'desc')->first();
        if ($newestBot) {
            if ($newestBot->order_id) {
                $order = Order::find($newestBot->order_id);
                if ($order) {
                    $order->status = 'pending';
                    $order->save();
                }
            }
            $newestBot->delete();
        }

        return redirect('/manager');
    }
    public function processOrders()
    {
        Log::info("Starting processOrders method");

        do {
            // Fetch all idle bots
            $bots = Bot::where('status', 'idle')->get();
            Log::info("Idle bots count: " . $bots->count());
            if ($bots->isEmpty()) {
                Log::info("No idle bots available. Skipping job dispatch.");
                return;
            }
            $ordersProcessed = false; // To track whether any orders were processed in this loop iteration

            foreach ($bots as $bot) {
                // Double-check if the bot is still idle
                if ($bot->status !== 'idle') {
                    Log::info("Bot {$bot->id} is not idle, skipping.");
                    continue; // Skip if the bot is not idle anymore
                }

                // Process orders while the bot is idle
                while ($bot->status === 'idle') {
                    // Fetch the first available VIP order
                    $order = Order::where('status', 'pending')
                        ->where('type', 'vip')
                        ->orderBy('created_at')
                        ->first();

                    // If no VIP orders are available, check for normal orders
                    if (!$order) {
                        $order = Order::where('status', 'pending')
                            ->where('type', 'normal')
                            ->orderBy('created_at')
                            ->first();
                    }

                    // If no orders are found, break out of the loop for this bot
                    if (!$order) {
                        Log::info("No more orders available to process for bot {$bot->id}.");
                        break;
                    }

                    // Handle versioning to avoid race conditions
                    $expectedVersion = $order->version;

                    Log::info("Attempting to update order {$order->id}. Bot status: {$bot->status}, Order status: {$order->status}, Order version: {$order->version}, Expected version: {$expectedVersion}");

                    // Begin transaction to ensure atomic update of order status and version
                    \DB::transaction(function () use ($bot, $order, $expectedVersion, &$ordersProcessed) {
                        if ($bot->status === 'idle' && $order->status === 'pending' && $order->version === $expectedVersion) {
                            // Update order status to 'processing' and increment the version
                            $updated = Order::where('id', $order->id)
                                ->where('version', $expectedVersion)
                                ->update([
                                    'status' => 'processing',
                                    'version' => $expectedVersion + 1 // Increment version to avoid race conditions
                                ]);

                            // If update was successful, assign the order to the bot
                            if ($updated) {
                                Log::info("Order {$order->id} version updated successfully to " . ($expectedVersion + 1));
                                $bot->update([
                                    'status' => 'processing',
                                    'order_id' => $order->id
                                ]);

                                // Log the bot status after the update
                                $bot->refresh(); // Refresh the bot instance to get the latest data from the database
                                Log::info("Bot {$bot->id} status after update: {$bot->status}, order_id: {$bot->order_id}");

                                // Log before dispatching the job
                                Log::info("About to dispatch ProcessOrderJob for order {$order->id} with bot {$bot->id}");
                                ProcessOrderJob::dispatch($order, $bot->id);
                                // Log after dispatching the job
                                Log::info("Dispatched ProcessOrderJob for order {$order->id} with bot {$bot->id}");

                                // Mark that an order was processed
                                $ordersProcessed = true;

                                // Break out of the loop for this bot as it's now processing an order
                                return;
                            } else {
                                Log::warning("Failed to update version for order {$order->id}. Expected version: {$expectedVersion}, Current version: {$order->version}");
                            }
                        } else {
                            Log::warning("Order {$order->id} status or version mismatch. Bot status: {$bot->status}, Order status: {$order->status}, Order version: {$order->version}, Expected version: {$expectedVersion}");
                        }
                    });

                    // If no more orders are found, exit the loop
                    if (!$ordersProcessed) {
                        Log::info("No orders were processed in this iteration for bot {$bot->id}.");
                        break;
                    }
                }
            }

            // Check again for pending orders; exit loop if none left
            $pendingOrders = Order::where('status', 'pending')->get();
            $pendingOrdersExist = $pendingOrders->isNotEmpty();
            Log::info("#############");
            if ($pendingOrdersExist) {
                sleep(1);
                Log::info("Pending orders exist:");
                foreach ($pendingOrders as $pendingOrder) {
                    Log::info("Order ID: {$pendingOrder->id}, Type: {$pendingOrder->type}, Status: {$pendingOrder->status}");
                }
            } else {
                Log::info("No pending orders exist.");
            }

        } while ($pendingOrdersExist); // Continue until no more pending orders

        Log::info("Exiting processOrders method");
    }
    
}