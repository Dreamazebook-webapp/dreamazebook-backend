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
        Schema::create('picbook', function (Blueprint $table) {
            $table->bigInteger('id', true);
            $table->string('bookname', 30);
            $table->bigInteger('pid')->default(0);
            $table->tinyInteger('gender')->default(0);
            $table->string('language', 10)->default('en');
            $table->integer('skincolor');
            $table->string('showpic', 90)->nullable();
            $table->string('intro', 300)->nullable();
            $table->string('description', 800)->nullable();
            $table->string('pricesymbol', 2)->nullable();
            $table->float('price', 6)->nullable();
            $table->string('currencycode', 10)->nullable();
            $table->integer('rating')->default(0);
            $table->integer('formid')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('picbook');
    }
};
