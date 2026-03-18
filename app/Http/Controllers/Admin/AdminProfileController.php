<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AdminProfileService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminProfileController extends Controller
{
    protected $service;

    public function __construct(AdminProfileService $service)
    {
        $this->service = $service;
    }

    public function update(Request $request)
    {
        $request->validate([
            'password' => 'required|min:6|confirmed',
        ]);

        $admin = Auth::user();

        if (!$admin) {
            return response()->json([
                'message' => 'Unauthorized.'
            ], 401);
        }

        $this->service->updatePassword($admin, $request->only('password'));

        return response()->json([
            'message' => 'Password updated successfully.'
        ]);
    }
}
