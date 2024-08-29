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
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->foreignId('category_id');
            $table->decimal('price',8,2)->nullable();
            $table->decimal('discount_price',8,2)->nullable();
            $table->foreignId('author_id');
            $table->integer('copies_owned');
            $table->string('book_image');
            $table->integer('status')->default(1)->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};
