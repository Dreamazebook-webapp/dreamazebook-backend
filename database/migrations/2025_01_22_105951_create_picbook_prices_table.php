<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('picbook_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('picbook_id')->constrained()->onDelete('cascade');
            $table->string('pricesymbol', 10);
            $table->decimal('price', 8, 2);
            $table->string('currencycode', 3);
            $table->dateTime('valid_from')->nullable();
            $table->dateTime('valid_until')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['picbook_id', 'currencycode']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('picbook_prices');
    }
};