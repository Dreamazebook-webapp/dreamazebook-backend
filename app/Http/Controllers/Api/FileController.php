<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class FileController extends Controller
{
    /**
     * 前台用户文件上传
     * 
     * @authenticated
     * @header Authorization Bearer your-token-here
     * 
     * @bodyParam file file required 要上传的文件，最大5MB
     * @bodyParam type string required 文件类型，可选值：avatar,aiface,document
     * @bodyParam folder string optional 子文件夹路径
     * 
     * @response 201 {
     *   "url": "http://your-domain.com/storage/user_uploads/1/avatar/xxx.jpg",
     *   "path": "user_uploads/1/avatar/xxx.jpg",
     *   "original_name": "photo.jpg",
     *   "file_name": "uuid.jpg",
     *   "mime_type": "image/jpeg",
     *   "size": 1024,
     *   "extension": "jpg"
     * }
     */
    public function userUpload(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'file' => [
                'required',
                'file',
                'max:5120', // 前台限制5MB
                'mimes:jpeg,png,jpg,gif,mp3,mp4,pdf,doc,docx'
            ],
            'type' => 'required|string|in:avatar,aiface,document', // 前台文件类型
            'folder' => 'nullable|string|max:100'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $file = $request->file('file');
            $type = $request->input('type');
            $folder = $request->input('folder', '');
            $userId = Auth::id();

            // 获取文件信息
            $originalName = $file->getClientOriginalName();
            $extension = $file->getClientOriginalExtension();
            $mimeType = $file->getMimeType();
            $size = $file->getSize();

            // 生成唯一文件名
            $fileName = Str::uuid() . '.' . $extension;

            // 构建存储路径（用户文件存储在 user_uploads 目录下）
            $path = trim("user_uploads/{$userId}/{$type}/{$folder}", '/');
            
            // 存储文件
            $filePath = Storage::disk('public')->putFileAs(
                $path,
                $file,
                $fileName
            );

            // 生成访问URL
            $url = Storage::disk('public')->url($filePath);

            return response()->json([
                'url' => $url,
                'path' => $filePath,
                'original_name' => $originalName,
                'file_name' => $fileName,
                'mime_type' => $mimeType,
                'size' => $size,
                'extension' => $extension
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => '文件上传失败',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 后台管理文件上传
     * 
     * @authenticated
     * @header Authorization Bearer your-admin-token-here
     * 
     * @bodyParam file file required 要上传的文件，最大20MB
     * @bodyParam type string required 文件类型，可选值：picbook,cover,content,resource
     * @bodyParam folder string optional 子文件夹路径
     */
    public function adminUpload(Request $request)
    {
        \Log::info('User:', ['user' => Auth::user()]);  // 添加这行来调试
        $validator = \Validator::make($request->all(), [
            'file' => [
                'required',
                'file',
                'max:20480', // 后台限制20MB
                'mimes:jpeg,png,jpg,gif,mp3,mp4,wav,pdf,doc,docx,xls,xlsx'
            ],
            'type' => 'required|string|in:picbook,cover,content,resource', // 后台文件类型
            'folder' => 'nullable|string|max:100'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $file = $request->file('file');
            $type = $request->input('type');
            $folder = $request->input('folder', '');

            // 获取文件信息
            $originalName = $file->getClientOriginalName();
            $extension = $file->getClientOriginalExtension();
            $mimeType = $file->getMimeType();
            $size = $file->getSize();

            // 生成唯一文件名
            $fileName = Str::uuid() . '.' . $extension;

            // 构建存储路径（管理文件存储在 admin_uploads 目录下）
            $path = trim("admin_uploads/{$type}/{$folder}", '/');
            
            // 存储文件
            $filePath = Storage::disk('public')->putFileAs(
                $path,
                $file,
                $fileName
            );

            // 生成访问URL
            $url = Storage::disk('public')->url($filePath);

            return response()->json([
                'url' => $url,
                'path' => $filePath,
                'original_name' => $originalName,
                'file_name' => $fileName,
                'mime_type' => $mimeType,
                'size' => $size,
                'extension' => $extension
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => '文件上传失败',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 删除文件
     * 
     * @authenticated
     * @header Authorization Bearer your-token-here
     * 
     * @bodyParam path string required 要删除的文件路径
     */
    public function destroy(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'path' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $path = $request->input('path');
            $userId = Auth::id();
            
            // 检查文件是否存在
            if (!Storage::disk('public')->exists($path)) {
                return response()->json(['message' => '文件不存在'], 404);
            }

            // 检查权限
            if (!Auth::user()->isAdmin()) {
                // 普通用户只能删除自己的文件
                if (!str_starts_with($path, "user_uploads/{$userId}/")) {
                    return response()->json(['message' => '没有权限删除此文件'], 403);
                }
            }

            // 删除文件
            Storage::disk('public')->delete($path);

            return response()->json(null, 204);
        } catch (\Exception $e) {
            return response()->json([
                'message' => '文件删除失败',
                'error' => $e->getMessage()
            ], 500);
        }
    }
} 