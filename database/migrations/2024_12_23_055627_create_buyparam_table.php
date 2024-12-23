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
        Schema::create('buyparam', function (Blueprint $table) {
            $table->bigInteger('id', true);
            $table->bigInteger('scid')->nullable();
            $table->string('firstname', 30)->nullable();
            $table->string('lastname', 30)->nullable();
            $table->string('recphoto', 90)->nullable();
            $table->string('language', 10)->nullable();
            $table->tinyInteger('gender')->nullable();
            $table->integer('skincolor')->nullable();
            $table->string('creatorname', 30)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('buyparam');
    }
};
