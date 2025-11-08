<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('zip_codes', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10);
            $table->unsignedBigInteger('settlement_id');  // Explicit típus
            $table->unsignedBigInteger('county_id');      // Explicit típus
            $table->timestamps();

            // Foreign key constraints külön
            $table->foreign('settlement_id')
                  ->references('id')
                  ->on('settlements')
                  ->onDelete('cascade');
                  
            $table->foreign('county_id')
                  ->references('id')
                  ->on('counties')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('zip_codes');
    }
};