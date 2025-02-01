<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class LanguageController extends Controller
{
    /**
     * 切换语言
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function switch(Request $request)
    {
        $language = $request->input('language');
        
        // 验证语言是否被支持
        if (!in_array($language, config('app.supported_locales', ['en', 'zh']))) {
            return response()->json([
                'message' => '不支持的语言',
                'status' => 'error'
            ], 422);
        }

        // 更新 session 中的语言设置
        session(['locale' => $language]);
        App::setLocale($language);

        return response()->json([
            'message' => '语言切换成功',
            'status' => 'success',
            'language' => $language
        ]);
    }

    /**
     * 获取当前语言
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function current()
    {
        return response()->json([
            'language' => App::getLocale(),
            'supported_locales' => config('app.supported_locales', ['en', 'zh'])
        ]);
    }
} 