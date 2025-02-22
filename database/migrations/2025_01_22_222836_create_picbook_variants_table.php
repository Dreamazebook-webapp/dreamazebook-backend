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
        Schema::create('picbook_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('picbook_id')->constrained()->onDelete('cascade');
            $table->string('language', 2)->comment('语言代码，如：en, zh');
            $table->tinyInteger('gender')->comment('性别：1-男，2-女');
            $table->tinyInteger('skincolor')->comment('肤色：1-白，2-黄，3-黑');
            $table->string('bookname', 255);
            $table->text('intro')->nullable()->comment('简介');
            $table->text('description')->nullable()->comment('详细描述');
            $table->string('cover')->comment('封面图片URL');
            $table->decimal('price', 8, 2)->default(0.00)->comment('变体特定价格');
            $table->string('pricesymbol', 10)->default('$');
            $table->string('currencycode', 3)->default('USD');
            $table->json('tags')->nullable()->comment('变体特定标签');
            $table->tinyInteger('status')->default(1)->comment('状态：0-草稿，1-已发布，2-已下架');
            $table->timestamps();
            $table->softDeletes();

            // 一个绘本的每种语言、性别和肤色组合必须唯一
            $table->unique(['picbook_id', 'language', 'gender', 'skincolor'], 'unique_variant');
            
            // 添加索引以提高查询性能
            $table->index(['language', 'gender', 'skincolor']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('picbook_variants');
    }
};
