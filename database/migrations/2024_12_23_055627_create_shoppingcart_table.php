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
        Schema::create('shoppingcart', function (Blueprint $table) {
            $table->bigInteger('id', true);
            $table->bigInteger('uid')->nullable();
            $table->string('cid', 50)->nullable();
            $table->bigInteger('pbid');
            $table->string('pname', 30)->nullable();
            $table->string('pricesymbol', 2)->nullable();
            $table->float('price', 6)->nullable();
            $table->string('currencycode', 10)->nullable();
            $table->tinyInteger('gflag')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shoppingcart');
    }
};
