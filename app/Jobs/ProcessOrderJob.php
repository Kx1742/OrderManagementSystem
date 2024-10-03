<?php

namespace App\Jobs;

use App\Models\Order;
use App\Models\Bot;
use App\Http\Controllers\OrderController;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessOrderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $order;
    protected $botId;

    // Set the timeout for the job (in seconds)
    public $timeout = 120;

    // Set the maximum number of attempts for the job
    public $tries = 5;

    public function __construct(Order $order = null, $botId = null)
    {
        $this->order = $order;
        $this->botId = $botId;
    }

    public function handle()
    {
        Log::info("Order handle job");

        if ($this->order && $this->botId) {
            Log::info("Processing order {$this->order->id} with bot {$this->botId}");

            $bot = Bot::find($this->botId);
            if ($bot && $bot->status === 'processing') {
                try {
                    // Simulate order processing with countdown
                    Log::info("Order {$this->order->id} being processed by bot {$this->botId}");
                    for ($i = 10; $i > 0; $i--) {
                        Log::info("Processing order {$this->order->id}: {$i} seconds remaining");
                        sleep(1);
                    }

                    // Update order status to completed
                    $this->order->update(['status' => 'completed']);

                    // Update bot status to idle
                    $bot->update(['status' => 'idle', 'order_id' => null]);

                    Log::info("Order {$this->order->id} processed successfully by bot {$this->botId}");
                } catch (\Exception $e) {
                    Log::error("Error processing order {$this->order->id} with bot {$this->botId}: " . $e->getMessage());
                    throw $e; // Re-throw the exception to mark the job as failed
                }
            } else {
                Log::warning("Bot {$this->botId} is not in processing state or does not exist.");
            }
        } else {
            Log::warning("Order or bot ID is missing. Calling processOrders from OrderController.");
            // Call processOrders method from OrderController
            $orderController = new OrderController();
            $orderController->processOrders();
        }
    }

    /**
     * Handle a job failure.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function failed(\Exception $exception)
    {
        Log::error("Job failed for order {$this->order->id} with bot {$this->botId}: " . $exception->getMessage());
    }
}