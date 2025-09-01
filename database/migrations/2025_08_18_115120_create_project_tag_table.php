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
        Schema::create('project_tag', function (Blueprint $table) {
            // المفتاح الأول: project_id
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            // المفتاح الثاني: tag_id
            $table->foreignId('tag_id')->constrained()->onDelete('cascade');

            // تحديد أن الزوج (project_id, tag_id) يجب أن يكون فريداً
            $table->primary(['project_id', 'tag_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_tag');
    }
};
