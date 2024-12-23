<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class IndexController extends Controller
{
    public function index(Request $request)
    {
        // 获取菜单
        $menus = $this->getMenus();

        // 获取分类
        [$len_categories, $categories] = $this->getCategories();

        return response()->json([
            'menus' => $menus,
            'len_categories' => $len_categories,
            'categories' => $categories,
        ]);
    }

    public function categories(Request $request)
    {
        // 获取菜单
        $menus = $this->getMenus();

        // 获取分类
        [$len_categories, $categories] = $this->getCategories();

        // 获取分类 ID
        $bcid = $request->input('bcid');

        // 查询书籍列表
        $booklist = $this->getBooksByCategory($bcid);

        return response()->json([
            'menus' => $menus,
            'len_categories' => $len_categories,
            'categories' => $categories,
            'booklist' => $booklist,
        ]);
    }

    /**
     * 获取菜单列表
     *
     * @return array
     */
    private function getMenus()
    {
        // 获取所有菜单项
        $menus = DB::table('menu')->orderBy('pid')->get();

        // 递归构建多层级菜单
        $menuTree = $this->buildMenu($menus);

        // 返回构建的菜单数据
        return $menuTree;
    }

    
     /**
     * 获取菜单列表
     *
     * @return array
     */
    // 递归构建多层级菜单
    private function buildMenu($menus, $parentId = 0)
    {
        $result = [];
        foreach ($menus as $menu) {
            if ($menu->pid == $parentId) {
                // 获取当前菜单的子菜单
                $menu->children = $this->buildMenu($menus, $menu->id);
                $result[] = $menu;
            }
        }
        return $result;
    }

    /**
     * 获取分类列表
     *
     * @return array
     */
    private function getCategories()
    {
        // 获取所有分类
        $categories = DB::table('bcategory')->orderBy('id')->get();

        // 构建分类树
        $categoryTree = $this->buildTree($categories);

        // 获取顶级分类数量
        $len_categories = count($categoryTree);

        return [$len_categories, $categoryTree];
    }

    /**
     * 根据分类 ID 查询书籍
     *
     * @param int $bcid
     * @return \Illuminate\Support\Collection
     */
    private function getBooksByCategory($bcid)
    {
        $sql = "
            SELECT pb.id, pb.bookname, bc.bcname
            FROM book2category b2c
            JOIN picbook pb ON b2c.pbid = pb.id
            JOIN bcategory bc ON b2c.bcid = bc.id
            WHERE b2c.bcid = ? AND bc.id = ?
        ";

        return DB::select($sql, [$bcid, $bcid]);
    }

    /**
     * 通用的递归构建树方法
     *
     * @param  \Illuminate\Support\Collection $items
     * @param  int $parentId
     * @return array
     */
    private function buildTree($items, $parentId = 0)
    {
        $tree = [];
        foreach ($items as $item) {
            if ($item->pid == $parentId) {
                $item->child = $this->buildTree($items, $item->id);

                // 设置 cflag：0 无子分类，1 有一层子分类，2 有两层子分类
                $item->cflag = !empty($item->child) ? 1 : 0;
                foreach ($item->child as $subItem) {
                    if (!empty($subItem->child)) {
                        $item->cflag = 2;
                        break;
                    }
                }

                $tree[] = $item;
            }
        }
        return $tree;
    }
}