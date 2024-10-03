<?php

namespace App\Models;

use Swoole\Coroutine;

class OrderManager
{
    private static $orderCount = 0;
    private static $pendingOrders = [];
    private static $completedOrders = [];
    private static $bots = [];

    public static function addNormalOrder()
    {
        self::$orderCount++;
        $newOrder = [
            'id' => self::$orderCount,
            'type' => 'normal',
            'status' => 'pending'
        ];
        self::$pendingOrders[] = $newOrder;
        self::processOrders();
    }

    public static function addVipOrder()
    {
        self::$orderCount++;
        $newOrder = [
            'id' => self::$orderCount,
            'type' => 'vip',
            'status' => 'pending'
        ];
        array_unshift(self::$pendingOrders, $newOrder);
        self::processOrders();
    }

    public static function addBot()
    {
        $botId = count(self::$bots) + 1;
        $newBot = [
            'id' => $botId,
            'status' => 'IDLE'
        ];
        self::$bots[] = $newBot;
        self::processOrders();
    }

    public static function removeBot()
    {
        if (!empty(self::$bots)) {
            array_pop(self::$bots);
        }
    }

    // Process pending orders with available bots
    public static function processOrders()
    {
        foreach (self::$bots as &$bot) {
            if ($bot['status'] === 'IDLE') {
                $order = self::getNextOrder();
                if ($order) {
                    $bot['status'] = 'PROCESSING';
                    $order['status'] = 'PROCESSING';

                    // Simulate processing (this should run concurrently for each bot)
                    Coroutine::create(function () use (&$bot, &$order) {
                        sleep(10); // Simulate 10-second processing
                        $order['status'] = 'COMPLETE';
                        $bot['status'] = 'IDLE';
                        self::$completedOrders[] = $order;
                        self::processOrders(); // Check for more orders to process
                    });
                }
            }
        }
    }

    private static function getNextOrder()
    {
        return array_shift(self::$pendingOrders);
    }

    public static function getPendingOrders()
    {
        return self::$pendingOrders;
    }

    public static function getCompletedOrders()
    {
        return self::$completedOrders;
    }

    public static function getBots()
    {
        return self::$bots;
    }
}