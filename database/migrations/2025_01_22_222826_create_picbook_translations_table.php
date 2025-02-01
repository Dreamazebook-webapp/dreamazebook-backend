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
        Schema::create('picbook_translations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('picbook_id');
            $table->string('language');
            $table->string('bookname');
            $table->string('intro')->nullable();
            $table->string('description')->nullable();
            $table->string('pricesymbol');
            $table->float('price');
            $table->string('currencycode');
            $table->string('cover');
            $table->string('tags');
            $table->string('content', 1000);
            $table->foreign('picbook_id')->references('id')->on('picbooks')->onDelete('cascade');
            $table->unique(['picbook_id', 'language']);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('picbook_translations');
    }
};
