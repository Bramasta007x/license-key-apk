<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Services\RegisterService;
use Illuminate\Http\Request;

class RegisterController extends Controller
{
    public function __construct(protected RegisterService $service) {}

    public function register(Request $request)
    {
        $validated = $request->validate([
            'registrant.name' => 'required|string|max:255',
            'registrant.email' => 'required|email|max:255',
            'registrant.phone' => 'required|string|max:20',
            'payment_method' => 'required|in:midtrans,manual_transfer',
            'product_id' => 'nullable|uuid|exists:products,id',
            'total_cost' => 'required|numeric|min:1',
        ]);

        $result = $this->service->register($validated);

        return response()->json($result, 201);
    }
}
