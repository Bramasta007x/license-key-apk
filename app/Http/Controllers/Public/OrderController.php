<?php

namespace App\Http\Controllers\Public;

use App\Services\OrderService;
use App\Http\Controllers\Controller;

class OrderController extends Controller
{
    public function __construct(private OrderService $service) {}

    /**
     * GET /orders/{order_number}/status
     * Cek status order dan detail terkait
     */
    public function status(string $orderNumber)
    {
        $data = $this->service->getOrderStatus($orderNumber);

        if (!$data) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }
}
