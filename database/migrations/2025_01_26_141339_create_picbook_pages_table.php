<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('picbook_pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('picbook_id')->constrained()->onDelete('cascade')->comment('绘本ID');
            $table->integer('page_number')->comment('页码');
            $table->string('image_url')->comment('页面图片URL');
            $table->string('none_skin')->nullable()->comment('无肤色版本图片URL');
            $table->json('elements')->nullable()->comment('页面可编辑元素配置');
            $table->boolean('is_choices')->default(false)->comment('是否是选择页面(8选4)');
            $table->string('question')->nullable()->comment('选择页面的问题');
            $table->tinyInteger('status')->default(1)->comment('状态: 0-禁用, 1-启用');
            $table->boolean('is_ai_face')->default(false)->comment('是否需要AI换脸');
            $table->string('mask_image_url')->nullable()->comment('AI换脸遮罩图片URL');
            $table->boolean('has_replaceable_text')->default(false)->comment('是否包含可替换文字');
            $table->json('text_elements')->nullable()->comment('可替换文字配置');
            $table->timestamps();
            $table->softDeletes();

            // 索引优化
            $table->index(['picbook_id', 'page_number']); // 常用查询组合
            $table->index(['status', 'created_at']); // 状态筛选
        });

        Schema::create('picbook_page_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('page_id')->comment('关联的页面ID')->constrained('picbook_pages')->onDelete('cascade');
            $table->string('language', 5)->comment('语言代码(如: zh-CN, en-US)');
            $table->tinyInteger('gender')->default(1)->comment('性别: 1-男, 2-女');
            $table->tinyInteger('skincolor')->default(1)->comment('肤色: 1-黄, 2-白, 3-黑');
            $table->string('image_url')->comment('页面图片URL');
            $table->text('content')->comment('页面文本内容');
            $table->json('choice_options')->nullable()->comment('选择题选项');
            $table->string('question')->nullable()->comment('选择题问题翻译');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['page_id', 'language', 'gender', 'skincolor'], 'unique_page_variant');
            $table->index('language');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('picbook_page_variants');
        Schema::dropIfExists('picbook_pages');
    }
};
