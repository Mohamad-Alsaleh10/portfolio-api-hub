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
        Schema::table('users', function (Blueprint $table) {
            // إضافة عمود profile_picture (صورة الملف الشخصي)
            // يمكن أن يكون null في البداية
            $table->string('profile_picture')->nullable()->after('password');

            // إضافة عمود bio (وصف المستخدم)
            // يمكن أن يكون نصًا طويلاً ويمكن أن يكون null
            $table->text('bio')->nullable()->after('profile_picture');

            // إضافة عمود role (الدور) بقيمة افتراضية 'user'
            // يمكن أن يكون 'user' أو 'admin'
            $table->string('role')->default('user')->after('bio');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // عند التراجع عن الترحيل (rollback)، نقوم بحذف الأعمدة المضافة
            $table->dropColumn(['profile_picture', 'bio', 'role']);
        });
    }
};

