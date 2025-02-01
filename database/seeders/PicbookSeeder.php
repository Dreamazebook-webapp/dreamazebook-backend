<?php

namespace Database\Seeders;

use App\Models\Picbook;
use App\Models\PicbookPage;
use Illuminate\Database\Seeder;

class PicbookSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 创建一本绘本
        $picbook = Picbook::create([
            'default_name' => 'Adventure in the Forest',
            'pricesymbol' => '$',
            'price' => 9.99,
            'currencycode' => 'USD',
            'total_pages' => 10,
            'default_cover' => 'covers/forest-adventure.jpg',
            'rating' => 5.00,
            'supported_languages' => ['en', 'zh'],
            'supported_genders' => [1, 2],  // 1: 男, 2: 女
            'supported_skincolors' => [1, 2, 3],  // 不同肤色选项
            'tags' => ['adventure', 'nature', 'animals'],
            'status' => Picbook::STATUS_PUBLISHED
        ]);

        // 创建普通页面
        $page1 = PicbookPage::create([
            'picbook_id' => $picbook->id,
            'page_number' => 1,
            'gender' => 1,
            'skincolor' => 1,
            'image_url' => 'pages/page1.jpg',
            'elements' => [
                'character' => ['x' => 100, 'y' => 200],
                'text' => ['x' => 300, 'y' => 400]
            ],
            'is_choices' => false,
            'status' => PicbookPage::STATUS_PUBLISHED
        ]);

        // 添加页面翻译
        $page1->translations()->createMany([
            [
                'language' => 'en',
                'content' => 'Once upon a time, in a magical forest...',
                'is_choices' => false
            ],
            [
                'language' => 'zh',
                'content' => '从前，在一片神奇的森林里...',
                'is_choices' => false
            ]
        ]);

        // 创建8选4页面
        $page2 = PicbookPage::create([
            'picbook_id' => $picbook->id,
            'page_number' => 2,
            'gender' => 1,
            'skincolor' => 1,
            'image_url' => 'pages/page2.jpg',
            'elements' => [
                'character' => ['x' => 150, 'y' => 250],
                'text' => ['x' => 350, 'y' => 450]
            ],
            'is_choices' => true,
            'question' => 'What animal did you see in the forest?',
            'status' => PicbookPage::STATUS_PUBLISHED
        ]);

        // 添加8选4页面翻译
        $page2->translations()->createMany([
            [
                'language' => 'en',
                'content' => 'You walked into the forest and saw different animals...',
                'is_choices' => true,
                'question' => 'What animal did you see in the forest?'
            ],
            [
                'language' => 'zh',
                'content' => '你走进森林，看到了不同的动物...',
                'is_choices' => true,
                'question' => '你在森林里看到了什么动物？'
            ]
        ]);

        // 创建不同性别和肤色的变体页面
        $page1Variants = [
            ['gender' => 1, 'skincolor' => 2],
            ['gender' => 1, 'skincolor' => 3],
            ['gender' => 2, 'skincolor' => 1],
            ['gender' => 2, 'skincolor' => 2],
            ['gender' => 2, 'skincolor' => 3],
        ];

        foreach ($page1Variants as $variant) {
            $variantPage = $page1->replicate();
            $variantPage->gender = $variant['gender'];
            $variantPage->skincolor = $variant['skincolor'];
            $variantPage->image_url = "pages/page1_{$variant['gender']}_{$variant['skincolor']}.jpg";
            $variantPage->save();

            // 复制翻译
            foreach ($page1->translations as $translation) {
                $variantPage->translations()->create($translation->toArray());
            }
        }
    }
} 