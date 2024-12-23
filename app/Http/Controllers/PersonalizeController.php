<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;

class PersonalizeController extends Controller
{
    // 显示个性化页面
    public function showCharacterForm(Request $request)
    {
        // 获取传递的 book_id 参数
        $bookid = $request->query('bookid');
        $language = $request->query('language');

        // 验证 book_id 是否存在
        if (!$bookid) {
            return response()->json(['error' => 'Book ID is required.'], 400);
        }

        if (!in_array($language, ['en', 'fr', 'es'])) {
            return response()->json(['error' => 'Unsupported language.'], 400);
        }

        // 从数据库获取书籍信息
        $book = DB::table('picbook')
            ->where('id', $bookid)
            ->first();
        if (!$book) {
            return response()->json(['error' => 'Book not found.'], 404);
        }

        $pagepics = DB::table('bookpics')
            ->where('pbid', $bookid) // Ensure `$bookid` is passed correctly
            ->orderBy('pagenum')
            ->get();
        
        if ($pagepics->isEmpty()) {
            return response()->json(['error' => 'No pages found for this book.'], 404);
        }

        // 查询书籍及模板信息
        $bookform = DB::table('picbook as pb')
            ->join('bookforms as bf', 'pb.formid', '=', 'bf.id')
            ->where('pb.id', $bookid)
            ->select('pb.id as bookid', 'pb.bookname', 'bf.template')
            ->first();
        // 验证书籍是否存在
        if (!$bookform) {
            return response()->json(['error' => 'Book form not found.'], 404);
        }

        $template = $bookform->template; // 动态获取模板名称

        // 加载对应模板视图并传递数据
        return response()->json([
            'book' => $book,
            'bookform' => $bookform,
            'bookid' => $bookid,
            'language' => $language,
            'pagepics' => $pagepics
        ]);
    }

    // 保存个性化数据
    public function saveCharacterInfo(Request $request)
    {
        $data = $request->validate([
            'bookid' => 'required|integer',
            'language' => 'required|string|max:10',
            'first_name' => 'required|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'gender' => 'required|string|in:1,2',
            'skin_color' => 'required|string|in:0,1,2',
            'photo' => 'required|image|mimes:jpeg,png,gif|max:2048',
        ]);


        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
    
            // Extra security check for image validity
            $imageInfo = getimagesize($file);
            if ($imageInfo === false) {
                return redirect()->back()->withErrors('Uploaded file is not a valid image.');
            }
    
            // Save the file and store the path
            $photoPath = $file->store('uploads/photos', 'public');
            $data['photo_path'] = $photoPath;
        }

        $filename = uniqid() . '.' . $file->getClientOriginalExtension();

        // 检查用户登录状态或生成 cookie_id
        $user = Auth::user();
        $uid = $user ? $user->id : null;

        $cookieId = $request->cookie('cookie_id');
        if (!$cookieId) {
            $cookieId = uniqid('guest_', true);
            Cookie::queue('cookie_id', $cookieId, 60 * 24 * 30); // 设置 30 天有效期
        }

        // 查找子书信息
        $childBook = DB::table('picbook')
            ->where('pid', $data['bookid'])
            ->where('gender', $data['gender'])
            ->where('skincolor', $data['skin_color'])
            ->where('language', $data['language'])
            ->first();

        if (!$childBook) {
            return redirect()->back()->withErrors(['message' => 'No matching book found for the given customization.']);
        }

        // 检查购物车中是否已存在该书
        $existingCartItem = DB::table('shoppingcart')
            ->where('pbid', $childBook->id)
            ->where(function ($query) use ($uid, $cookieId) {
                if ($uid) {
                    $query->where('uid', $uid);
                } else {
                    $query->where('cid', $cookieId);
                }
            })
            ->first();

        if ($existingCartItem) {
            return redirect()->route('personalize.preview', ['bookid' => $childBook->id])
                            ->with('warning', 'This item is already in your cart.');
        }
        
        // 修改子书书名，添加用户输入的名字作为后缀
        $firstName = $data['first_name'];
        $updatedBookName = $childBook->bookname . ' - ' . $firstName;

        // 插入购物车并获取流水 ID
        $cartId = DB::table('shoppingcart')->insertGetId([
            'uid' => $uid,
            'cid' => $uid ? null : $cookieId,
            'pbid' => $childBook->id,
            'pname' => $updatedBookName, // 更新后的书名
            'pricesymbol' => $childBook->pricesymbol,
            'price' => $childBook->price,
            'currencycode' => $childBook->currencycode,
            'gflag' => 0, // 设置为未生成状态
        ]);

        // 插入购物车参数到 buyparam 表
        DB::table('buyparam')->insert([
            'scid' => $cartId,            // 购物车 ID
            'firstname' => $data['first_name'], 
            'lastname' => $data['last_name'], 
            'recphoto' => $data['photo_path'], 
            'language' => $data['language'], 
            'gender' => $data['gender'], 
            'skincolor' => $data['skin_color'], 
            'creatorname' => $user ? $user->name : 'Guest',
        ]);

        // Redirect to the preview page with the book ID
        return response()->json([
            'success' => 'Book added to cart successfully.',
            'book_id' => $childBook->id,
        ]);
    }


}
