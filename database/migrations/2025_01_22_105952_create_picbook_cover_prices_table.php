<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('picbook_cover_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('picbook_id')->constrained()->onDelete('cascade');
            $table->string('cover_type');  // 封面类型，如 'special', 'holiday' 等
            $table->string('pricesymbol', 10);
            $table->decimal('extra_price', 8, 2); // 额外费用
            $table->string('currencycode', 3);
            $table->dateTime('valid_from')->nullable();
            $table->dateTime('valid_until')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['picbook_id', 'cover_type', 'currencycode']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('picbook_prices');
    }
};