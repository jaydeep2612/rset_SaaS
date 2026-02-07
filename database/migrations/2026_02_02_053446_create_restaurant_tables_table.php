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
       Schema::create('restaurant_tables', function (Blueprint $table) {
    $table->bigIncrements('id');
    $table->foreignId('restaurant_id')->constrained()->restrictOnDelete();
    $table->string('table_number');
    $table->string('qr_path')->nullable()->after('qr_token');
    $table->string('qr_token')->unique();
    $table->unsignedSmallInteger('seating_capacity')->default(1);
    $table->boolean('is_active')->default(true);
    $table->timestamps();

    $table->unique(['restaurant_id', 'table_number']);
});


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('restaurant_tables');
    }
};
