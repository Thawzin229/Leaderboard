<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('point_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedInteger('points');
            $table->enum('action_type', ['Earn', 'Deduct'])->index();
            $table->string('description', 500)->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index(['created_at', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('point_transactions');
    }
};
