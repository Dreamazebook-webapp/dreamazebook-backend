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
            $table->foreignId('picbook_id')->constrained()->onDelete('cascade');
            $table->string('language', 2);
            $table->string('bookname');
            $table->text('intro')->nullable();
            $table->text('description')->nullable();
            $table->string('cover');
            $table->json('tags')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['picbook_id', 'language']);
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
