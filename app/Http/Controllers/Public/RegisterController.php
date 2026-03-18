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
            'registrant.name' => 'required|string',
            'registrant.email' => 'required|email',
            'registrant.phone' => 'required|string',
            'registrant.ticket_id' => 'required_without:attendees|uuid|exists:tickets,id',
            'registrant.gender' => 'nullable|in:M,F',
            'registrant.birthdate' => 'nullable|date',
            'attendees' => 'nullable|array|max:4',
            'attendees.*.ticket_id' => 'required|uuid|exists:tickets,id',
            'attendees.*.name' => 'required|string',
            'attendees.*.gender' => 'nullable|in:M,F',
            'attendees.*.birthdate' => 'nullable|date',
            'payment_method' => 'required|in:midtrans_snap',
        ]);

        $result = $this->service->register($validated);

        return response()->json($result, 201);
    }
}
