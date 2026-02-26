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
        Schema::create('orders', function (Blueprint $table) {
    $table->bigIncrements('id');
    $table->foreignId('restaurant_id')->constrained()->restrictOnDelete();
    $table->foreignId('restaurant_table_id')->constrained()->restrictOnDelete();
    $table->foreignId('qr_session_id')->constrained()->restrictOnDelete();
    $table->string('status');
    $table->string('customer_name')->nullable();
    $table->text('notes')->nullable();
    //$table->decimal('subtotal', 10, 2);
    $table->decimal('total_amount', 10, 2);
    $table->timestamps();

    $table->index(['restaurant_id', 'status']);
});


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
