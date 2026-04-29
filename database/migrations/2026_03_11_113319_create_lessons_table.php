<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('lessons', function (Blueprint $table) {
            $table->id();
            $table->text('name');
            $table->string('uuid');
            $table->foreignId('group_id')->constrained('groups')->cascadeOnDelete();
            $table->foreignId('level_id')->nullable()->constrained('levels')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('semester_id')->nullable()->constrained('semesters')->cascadeOnDelete();
            $table->foreignId('edu_year_id')->nullable()->constrained('edu_years')->cascadeOnDelete();
            $table->foreignId('account_id')->nullable()->constrained('accounts')->cascadeOnDelete();
            $table->timestamp('exam_date')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->enum('status', ['0', '1', '2'])->default('0');
            // 0 - yaratilgan, 1 - jarayonda, 2 - yakunlangan
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lessons');
    }
};
