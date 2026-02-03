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
        Schema::create('restaurants', function (Blueprint $table) {
    $table->bigIncrements('id');
    $table->string('name');
    $table->string('slug')->unique();
    $table->string('logo_path')->nullable();
    $table->unsignedInteger('user_limits')->default(0);
    $table->boolean('is_active')->default(true);
    $table->unsignedBigInteger('created_by')->nullable();
    //$table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
    $table->timestamps();
    $table->index('is_active');
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('restaurants');
    }
};
