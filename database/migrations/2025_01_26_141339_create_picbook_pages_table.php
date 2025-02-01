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
            $table->unsignedBigInteger('picbook_id');
            $table->integer('page_number');
            $table->tinyInteger('gender')->default(1);
            $table->integer('skincolor')->default(1);
            $table->string('image_url');
            $table->json('elements')->nullable()->comment('页面上的可编辑元素，如文字位置等');
            $table->boolean('is_choices')->default(false)->comment('是否是8选4页面');
            $table->string('question')->nullable()->comment('当前页面的问题');
            $table->tinyInteger('status')->default(1);
            $table->boolean('is_ai_face')->default(false)->comment('是否需要AI换脸');
            $table->string('mask_image_url')->nullable()->comment('遮罩图片URL');
            $table->boolean('has_replaceable_text')->default(false)->comment('是否有可替换的文字');
            $table->json('text_elements')->nullable()->comment('文字元素配置，包含位置、字体、颜色等');
            
            $table->timestamps();
            $table->softDeletes();  // 添加软删除
             // 外键约束
             $table->foreign('picbook_id')->references('id')->on('picbooks')->onDelete('cascade');
             // 确保同一本书的同一页面不会有重复的性别和肤色组合
             $table->unique(['picbook_id', 'page_number', 'gender', 'skincolor']);
        });

        // 创建页面文本内容的翻译表
        Schema::create('picbook_page_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('page_id')->constrained('picbook_pages')->onDelete('cascade');
            $table->string('language', 2);
            $table->text('content');
            $table->boolean('is_choices')->default(false);
            $table->string('question')->nullable();
            $table->timestamps();
            $table->softDeletes();  // 添加软删除
            $table->unique(['page_id', 'language']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('picbook_page_translations');
        Schema::dropIfExists('picbook_pages');
    }
};
