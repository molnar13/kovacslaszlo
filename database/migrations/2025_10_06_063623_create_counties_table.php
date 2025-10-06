<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('counties', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
            
            $table->index('name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('counties');
    }
};