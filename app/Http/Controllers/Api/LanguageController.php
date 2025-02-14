<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class LanguageController extends Controller
{
    use ApiResponse;

    /**
     * 切换语言
     */
    public function switch(Request $request): JsonResponse
    {
        $locale = $request->input('locale', App::getLocale());

        // 验证语言是否支持
        if (!in_array($locale, ['en', 'zh'])) {
            return $this->error(__('validation.in', ['attribute' => 'language']));
        }

        App::setLocale($locale);

        return $this->success(['locale' => $locale], __('messages.language_switched'));
    }

    /**
     * 获取当前语言
     */
    public function current(): JsonResponse
    {
        return $this->success([
            'locale' => App::getLocale(),
            'available_locales' => ['en', 'zh']
        ]);
    }
} 