<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table): void {
            $table->string('category')->default('General')->after('description');
            $table->string('level')->default('Todos los niveles')->after('category');
            $table->unsignedSmallInteger('duration_minutes')->default(60)->after('level');
            $table->boolean('is_featured')->default(false)->after('duration_minutes');
        });
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table): void {
            $table->dropColumn(['category', 'level', 'duration_minutes', 'is_featured']);
        });
    }
};
