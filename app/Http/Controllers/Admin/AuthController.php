<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Throwable;

class AuthController extends Controller
{
    public function __construct(private AuthService $auth) {}

    public function register(Request $request)
    {
        Log::info("[AuthController] Register request received", [
            "ip" => $request->ip(),
            "payload_preview" => $request->only(["email", "name"]),
        ]);

        try {
            $data = $request->validate([
                "name" => "required|string|max:100",
                "email" => "required|email|unique:admins,email",
                "password" => "required|string|min:6",
                "role" => "nullable|string",
            ]);

            Log::debug("[AuthController] Register validation success", [
                "email" => $data["email"],
            ]);

            $result = $this->auth->register($data);

            Log::info("[AuthController] Admin registered successfully", [
                "email" => $data["email"],
                "result" => [
                    "token_type" => $result["token_type"] ?? null,
                ],
            ]);

            return response()->json($result, 201);
        } catch (ValidationException $e) {
            Log::warning("[AuthController] Register validation failed", [
                "errors" => $e->errors(),
            ]);
            throw $e;
        } catch (Throwable $e) {
            Log::error("[AuthController] Register error", [
                "message" => $e->getMessage(),
                "line" => $e->getLine(),
                "file" => $e->getFile(),
                "trace" => collect(explode("\n", $e->getTraceAsString()))->take(
                    5,
                ),
            ]);
            return response()->json(
                [
                    "message" => "Internal server error during registration.",
                ],
                500,
            );
        }
    }

    public function login(Request $request)
    {
        Log::info("[AuthController] Login request received", [
            "ip" => $request->ip(),
            "payload_preview" => $request->only("email"),
        ]);

        try {
            $data = $request->validate([
                "email" => "required|email",
                "password" => "required|string",
            ]);

            Log::debug("[AuthController] Login validation passed", [
                "email" => $data["email"],
            ]);

            $result = $this->auth->login($data);

            Log::info("[AuthController] Admin login success", [
                "email" => $data["email"],
                "token_issued" => isset($result["token"]),
            ]);

            return response()->json($result);
        } catch (ValidationException $e) {
            Log::warning("[AuthController] Admin login failed: invalid credentials", [
                "errors" => $e->errors(),
            ]);

            return response()->json([
                "message" => "Email atau password salah."
            ], 401);
            throw $e;
        } catch (Throwable $e) {
            Log::error("[AuthController] Login process failed", [
                "message" => $e->getMessage(),
                "line" => $e->getLine(),
                "file" => $e->getFile(),
                "trace" => collect(explode("\n", $e->getTraceAsString()))->take(
                    5,
                ),
            ]);
            return response()->json(
                [
                    "message" => "Internal server error during login.",
                ],
                500,
            );
        }
    }

    public function logout(Request $request)
    {
        Log::info("[AuthController] Logout request received", [
            "ip" => $request->ip(),
            "user_id" => optional($request->user())->id,
        ]);

        try {
            $user = $request->user();
            if (!$user) {
                Log::warning(
                    "[AuthController] Logout attempted without user context",
                );
                return response()->json(
                    ["message" => "Not authenticated"],
                    401,
                );
            }

            $user->tokens()->delete();

            Log::info("[AuthController] Admin logout successful", [
                "id" => $user->id,
                "email" => $user->email,
            ]);

            return response()->json(["message" => "Logged out"]);
        } catch (Throwable $e) {
            Log::error("[AuthController] Logout failed", [
                "message" => $e->getMessage(),
                "line" => $e->getLine(),
                "file" => $e->getFile(),
                "trace" => collect(explode("\n", $e->getTraceAsString()))->take(
                    5,
                ),
            ]);
            return response()->json(
                [
                    "message" => "Internal server error during logout.",
                ],
                500,
            );
        }
    }
}
