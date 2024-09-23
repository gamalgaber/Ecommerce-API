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
        Schema::create('products', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('category_id')->constrained('categories')->references('id')->cascadeOnDelete();
            $table->foreignUuid('brand_id')->constrained('brands')->references('id')->cascadeOnDelete();
            $table->string('name', 180);
            $table->boolean('is_available')->default(false);
            $table->double('price', 8, 2);
            $table->integer('amount');
            $table->double('discount', 8, 2)->nullable();
            $table->text('image');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
