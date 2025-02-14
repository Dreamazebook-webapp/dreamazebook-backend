<?php

return [
    'language_switched' => '语言切换成功',
    'validation_error' => '验证错误',
    'route_not_found' => '路由未找到',
    'method_not_allowed' => '路由 :route 不支持 :method 方法。支持的方法：:allowed_methods。',
    'server_error' => '服务器内部错误',
    'unauthorized' => '未授权',
    'forbidden' => '禁止访问',
    'bad_request' => '错误的请求',
    'unknown_error' => '发生未知错误',
    'model_not_found' => '未找到指定的:model',
    'too_many_requests' => '请求过于频繁，请在 :seconds 秒后重试',
    'http_error' => 'HTTP请求错误',
    'validation' => [
        'required' => ':attribute 是必填项',
        'email' => ':attribute 必须是有效的电子邮件地址',
        'min' => [
            'string' => ':attribute 必须至少 :min 个字符',
        ],
        'max' => [
            'string' => ':attribute 不能超过 :max 个字符',
        ],
        'unique' => ':attribute 已经存在',
        'confirmed' => ':attribute 两次输入不一致',
    ],
];
