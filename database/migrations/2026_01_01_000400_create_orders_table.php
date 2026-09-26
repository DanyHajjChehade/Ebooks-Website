<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            // Orders are financial records: keep them if the customer deletes their account.
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status', 16)->default('pending');
            $table->unsignedInteger('subtotal_cents');
            $table->string('currency', 3)->default('usd');
            $table->string('stripe_checkout_session_id')->nullable()->unique();
            $table->string('stripe_payment_intent_id')->nullable()->index();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'paid_at']);
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('book_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->unsignedInteger('price_cents');
            $table->timestamps();

            $table->unique(['order_id', 'book_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
