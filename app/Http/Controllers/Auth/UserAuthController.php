<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserAuthController extends Controller
{
    use ApiResponse;

    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->success([
            'user' => $user,
            'token' => $token
        ], __('auth.register_success'), 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return $this->error(
                __('auth.failed'),
                ['email' => [__('auth.failed')]],
                401
            );
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->success([
            'user' => $user,
            'token' => $token
        ], __('auth.login_success'));
    }

    public function logout(): JsonResponse
    {
        auth()->user()->currentAccessToken()->delete();

        return $this->success(null, __('auth.logout_success'));
    }

    /**
     * 获取当前用户信息
     */
    public function me(): JsonResponse
    {
        $user = auth()->user();
        return $this->success($user, __('auth.user_info_success'));
    }

    /**
     * 修改个人信息
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'string|max:255',
            'avatar' => 'string|url'
        ]);

        if ($validator->fails()) {
            return $this->error(
                __('validation.failed'),
                $validator->errors(),
                422
            );
        }

        $user = auth()->user();
        $user->update($request->only(['name', 'avatar']));

        return $this->success($user, __('auth.profile_update_success'));
    }

    /**
     * 修改密码
     */
    public function updatePassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return $this->error(
                __('validation.failed'),
                $validator->errors(),
                422
            );
        }

        $user = auth()->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return $this->error(__('auth.password_incorrect'));
        }

        $user->update([
            'password' => Hash::make($request->password)
        ]);

        return $this->success(null, __('auth.password_update_success'));
    }
}
