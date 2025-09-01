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
        Schema::create('project_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade'); // مفتاح أجنبي لجدول المشاريع
            $table->string('file_path'); // مسار الملف (الصورة أو الفيديو)
            $table->string('file_type'); // نوع الملف (مثل 'image', 'video')
            $table->integer('order')->default(0); // لترتيب عرض الوسائط
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_media');
    }
};
