<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\ApiController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserController extends ApiController
{
    /**
     * 获取用户列表
     */
    public function index(Request $request)
    {
        $query = User::query();
        
        // 搜索条件
        if ($request->has('keyword')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->keyword . '%')
                  ->orWhere('email', 'like', '%' . $request->keyword . '%');
            });
        }
        
        // 状态筛选
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $users = $query->orderBy('created_at', 'desc')
            ->paginate($request->input('per_page', 15));

        return $this->success($users, __('user.list_success'));
    }

    /**
     * 禁用用户
     */
    public function disable($id)
    {
        $user = User::findOrFail($id);
        
        if ($user->status === 0) {
            return $this->error(__('user.already_disabled'));
        }

        $user->update(['status' => 0]);
        return $this->success(null, __('user.disable_success'));
    }

    /**
     * 启用用户
     */
    public function enable($id)
    {
        $user = User::findOrFail($id);
        
        if ($user->status === 1) {
            return $this->error(__('user.already_enabled'));
        }

        $user->update(['status' => 1]);
        return $this->success(null, __('user.enable_success'));
    }
} 