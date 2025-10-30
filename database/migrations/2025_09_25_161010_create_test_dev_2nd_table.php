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
        Schema::create('test_dev_2nd', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('amount')->unique()->comment('수량');
            $table->unsignedInteger('depth')->comment('뎁스');
            $table->unsignedInteger('count')->comment('인원수');
            $table->enum('condition', ['or', 'and'])->comment('조건');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('test_dev_2nd');
    }
};
