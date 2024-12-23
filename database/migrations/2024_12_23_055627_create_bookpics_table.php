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
        Schema::create('bookpics', function (Blueprint $table) {
            $table->bigInteger('id', true);
            $table->bigInteger('pbid');
            $table->integer('pagenum');
            $table->string('pagepic', 300);
            $table->tinyInteger('pflag')->default(0);
            $table->string('blankpic', 300)->nullable();
            $table->string('thumbnail', 300)->nullable();
            $table->tinyInteger('nameflag');
            $table->tinyInteger('nametype')->nullable();
            $table->tinyInteger('defflag')->default(0);
            $table->string('mergepic', 300)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookpics');
    }
};
