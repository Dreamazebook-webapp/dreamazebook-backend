<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

trait ApiResponse
{
    /**
     * 成功响应
     * 
     * @param mixed $data 响应数据
     * @param string $message 响应消息
     * @param int $code HTTP状态码
     * @return JsonResponse
     */
    public function success($data = null, string $message = 'Success', int $code = 200): JsonResponse
    {
        $response = [
            'success' => true,
            'code' => $code,
            'message' => $message,
        ];

        if ($data instanceof LengthAwarePaginator) {
            $response['data'] = $data->items();
            $response['meta'] = [
                'total' => $data->total(),
                'per_page' => $data->perPage(),
                'current_page' => $data->currentPage(),
                'last_page' => $data->lastPage()
            ];
        } elseif ($data instanceof Model || $data instanceof Collection || is_array($data)) {
            $response['data'] = $data;
        } elseif ($data !== null) {
            $response['data'] = $data;
        }

        return response()->json($response, $code);
    }

    /**
     * 错误响应
     * 
     * @param string $message 错误消息
     * @param mixed $errors 详细错误信息
     * @param int $code HTTP状态码
     * @return JsonResponse
     */
    public function error(string $message = 'Error', $errors = null, int $code = 400): JsonResponse
    {
        // 生产环境下对500错误进行特殊处理
        if (!config('app.debug') && $code >= 500) {
            $message = __('messages.server_error');
            $errors = null;
        }

        $response = [
            'success' => false,
            'code' => $code,
            'message' => $message,
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $code);
    }

    /**
     * 分页响应
     * 
     * @param LengthAwarePaginator $paginator
     * @param string $message
     * @return JsonResponse
     */
    public function paginate(LengthAwarePaginator $paginator, string $message = 'Success'): JsonResponse
    {
        return $this->success(
            $paginator->items(),
            $message,
            200,
            [
                'pagination' => [
                    'total' => $paginator->total(),
                    'per_page' => $paginator->perPage(),
                    'current_page' => $paginator->currentPage(),
                    'last_page' => $paginator->lastPage(),
                ]
            ]
        );
    }
}
