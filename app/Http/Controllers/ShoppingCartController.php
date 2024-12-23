<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class ShoppingCartController extends Controller
{
    public function addToCart(Request $request)
    {
        // 获取用户状态
        $user = Auth::user();
        $uid = $user ? $user->id : null; // 如果用户已登录，则获取用户 ID

        // 如果用户未登录，检查或生成 cookie_id
        $cookieId = $request->cookie('cookie_id');
        if (!$cookieId) {
            $cookieId = uniqid('guest_', true); // 生成唯一的 cookie_id
            Cookie::queue('cookie_id', $cookieId, 60 * 24 * 30); // 有效期 30 天
        }

        // 获取定制书本的信息
        $pbid = $request->input('pbid');
        $pname = $request->input('pname');
        $pricesymbol = $request->input('pricesymbol');
        $price = $request->input('price');
        $currencycode = $request->input('currencycode');

        // 验证请求数据
        $validated = $request->validate([
            'pbid' => 'required|integer',
            'pname' => 'required|string|max:30',
            'pricesymbol' => 'required|string|max:2',
            'price' => 'required|numeric|min:0',
            'currencycode' => 'required|string|max:10',
            'first_name' => 'required|string|max:255',
            'gender' => 'required|in:1,2',
            'skin_color' => 'required|in:0,1,2',
            'photo' => 'required|image|mimes:jpeg,png,gif|max:2048',
        ]);

        // 检查购物车中是否已存在此书
        $existingCartItem = DB::table('shoppingcart')
            ->where('pbid', $pbid)
            ->where(function ($query) use ($uid, $cookieId) {
                if ($uid) {
                    $query->where('uid', $uid);
                } else {
                    $query->where('cid', $cookieId);
                }
            })
            ->first();

        if ($existingCartItem) {
            // 如果购物车中已存在此书，可选择返回错误或更新信息
            return response()->json(['message' => 'Item already in cart'], 409);
        }

        // 将书本添加到购物车
        DB::table('shoppingcart')->insert([
            'uid' => $uid,
            'cid' => $uid ? null : $cookieId,
            'pbid' => $pbid,
            'pname' => $pname,
            'pricesymbol' => $pricesymbol,
            'price' => $price,
            'currencycode' => $currencycode,
            'gflag' => 0, // 初始状态为未生成
        ]);

        return response()->json(['message' => 'Item added to cart successfully'], 200);
    }

    public function showCart(Request $request)
    {
        $user = Auth::user();
        $uid = $user ? $user->id : null;
        $cookieId = $request->cookie('cookie_id');

        $cartItems = DB::table('shoppingcart')
            ->where(function ($query) use ($uid, $cookieId) {
                if ($uid) {
                    $query->where('uid', $uid);
                } else {
                    $query->where('cid', $cookieId);
                }
            })
            ->get();

        return response()->json(
            ['cartItems' => $cartItems
        ]);
    }

}
