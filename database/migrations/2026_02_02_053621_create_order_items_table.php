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
        Schema::create('order_items', function (Blueprint $table) {
    $table->bigIncrements('id');
    $table->foreignId('order_id')->constrained()->restrictOnDelete();
    $table->foreignId('menu_item_id')->constrained()->restrictOnDelete();
    $table->string('item_name');
    $table->decimal('unit_price', 10, 2);
    $table->unsignedInteger('quantity');
    $table->decimal('total_price', 10, 2);
    $table->text('notes')->nullable();
    $table->timestamps();
});


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
