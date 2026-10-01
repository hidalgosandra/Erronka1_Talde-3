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
        Schema::table('users', function (Blueprint $table): void {
            $table->boolean('is_teacher')->default(false)->after('is_admin');
        });

        Schema::table('courses', function (Blueprint $table): void {
            $table->foreignId('teacher_id')->nullable()->after('is_featured')->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('teacher_id');
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn('is_teacher');
        });
    }
};
