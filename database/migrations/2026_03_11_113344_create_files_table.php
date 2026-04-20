<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('files', function (Blueprint $table) {
            $table->id();
            $table->text('file_url')->nullable();
            $table->text('uuid')->unique();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('lesson_id')->constrained('lessons')->cascadeOnDelete();
            $table->float('overall')->default(0);
            $table->bigInteger('ticket_number')->default(0);
            $table->enum('status', ['0', '1', '2', '3'])->default('0');
            // 0 - yangi, 1 - tekshirilmoqda, 2 - yakunlangan, 3 - xatolik
            $table->enum('participant', ['0', '1'])->default('0');
            // 0 - imtihonda qatnashgan, 1 - imtihonda qatnashmagan
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('files');
    }
};
