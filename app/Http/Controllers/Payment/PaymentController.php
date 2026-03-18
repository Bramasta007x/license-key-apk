<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function __construct(private PaymentService $service) {}

    /**
     * Webhook callback dari Midtrans
     */
    public function webhook(Request $request)
    {
        try {
            $payload = $request->all();

            Log::info('[Webhook] Payment callback received', $payload);

            $result = $this->service->handleMidtransWebhook($payload);

            return response()->json([
                'success' => true,
                'message' => 'Webhook processed successfully',
                'data' => $result,
            ]);
        } catch (\Throwable $th) {
            Log::error('[Webhook] Payment callback failed', [
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Webhook processing failed',
                'error' => $th->getMessage(),
            ], 500);
        }
    }
}
