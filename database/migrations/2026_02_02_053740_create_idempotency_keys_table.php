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
        Schema::create('idempotency_keys', function (Blueprint $table) {
    $table->bigIncrements('id');
    $table->string('key');
    $table->string('scope');
    $table->unsignedBigInteger('reference_id')->nullable();
    $table->string('status');
    $table->timestamps();

    $table->unique(['key', 'scope']);
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('idempotency_keys');
    }
};
