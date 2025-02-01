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
            $table->unsignedBigInteger('picbook_id');
            $table->string('language', 10)->default('en');
            $table->string('bookname');
            $table->tinyInteger('gender')->default(1);
            $table->integer('skincolor');
            $table->string('cover', 90)->nullable();
            $table->tinyInteger('status')->default(1);
            $table->foreign('picbook_id')->references('id')->on('picbooks')->onDelete('cascade');
            $table->unique(['picbook_id', 'language', 'gender', 'skincolor']);
            $table->timestamps();
            $table->softDeletes();
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
