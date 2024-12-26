<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */
    'paths' => ['api/*', 'sanctum/csrf-cookie'], // 限制跨域路径
    'allowed_methods' => ['GET', 'POST'], // 仅允许必要的方法
    'allowed_origins' => ['http://localhost:3000'], // 仅允许前端来源
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['Content-Type', 'X-Requested-With', 'Authorization'], // 限制请求头
    'exposed_headers' => [],
    'max_age' => 3600, // 缓存时间，单位秒
    'supports_credentials' => true, // 如果需要凭证，确保配合限定来源



];
