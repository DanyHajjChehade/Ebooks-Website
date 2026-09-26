<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A customer's library: one row per (user, book) they may download.
     * The unique index is what makes order fulfilment idempotent.
     */
    public function up(): void
    {
        Schema::create('book_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('book_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'book_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('book_user');
    }
};
