<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('ai_face_tasks', function (Blueprint $table) {
            $table->id();
            $table->string('task_id')->nullable(); // AI服务返回的任务ID
            $table->string('status')->default('pending'); // pending, processing, completed, failed
            $table->string('input_image');
            $table->string('mask_image');
            $table->string('face_image');
            $table->string('result_image')->nullable();
            $table->text('error_message')->nullable();
            $table->foreignId('user_id')->constrained();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('ai_face_tasks');
    }
}; 