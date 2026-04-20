<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->string('email');
            $table->text('token');
            // Requests per Day
            $table->string('model')->default('gemini-3.1-pro-preview');
            $table->unsignedBigInteger('rpd')->default(250);
            $table->unsignedBigInteger('rpd_default')->default(250);
            $table->timestamp('reloaded_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->foreignId('department_id')->nullable()->constrained('departments')->cascadeOnDelete();
            // Bos, Processte, Islemeydi
            $table->enum('status', ['0', '1', '2'])->default('0');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
