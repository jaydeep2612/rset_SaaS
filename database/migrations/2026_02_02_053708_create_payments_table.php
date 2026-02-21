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
        Schema::create('payments', function (Blueprint $table) {
    $table->bigIncrements('id');
    $table->foreignId('order_id')->constrained()->restrictOnDelete();
    $table->decimal('amount', 10, 2);
    $table->enum('payment_method', ['cash', 'upi', 'card', 'online']);
    $table->enum('status', ['pending', 'paid', 'failed']);
    $table->string('transaction_reference')->nullable();
    $table->timestamp('paid_at')->nullable();
    $table->timestamps();

    $table->index(['order_id', 'status']);
});


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
