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
        Schema::create('picbooks', function (Blueprint $table) {
            $table->id();
            $table->string('default_name');  // 默认名称（通常是英文）
            $table->string('default_cover'); // 默认封面（通常是英文男性版本）
            $table->string('pricesymbol');
            $table->decimal('price', 10, 2);
            $table->string('currencycode', 3);
            $table->integer('total_pages');
            $table->decimal('rating', 3, 2)->default(5.00);
            $table->json('supported_languages');  // ['en', 'zh']
            $table->json('supported_genders');    // [1, 2]
            $table->json('supported_skincolors'); // [1, 2, 3]
            $table->string('none_skin')->nullable();
            $table->json('tags')->nullable();
            $table->boolean('has_choices')->default(false);  // 是否包含8选4
            $table->tinyInteger('choices_type')->default(0);  // 选择类型
            $table->boolean('has_qa')->default(false);  // 是否包含问答
            $table->tinyInteger('status')->default(0);  // 状态：草稿、已发布、已归档等
            $table->integer('character_count')->default(1)->comment('角色数量');
            $table->timestamps();
            $table->softDeletes();  // 添加软删除
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('picbooks');
    }
};
