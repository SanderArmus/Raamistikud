<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            // Can be null for guest checkout
            $table->unsignedBigInteger('user_id')->nullable();

            $table->string('first_name');
            $table->string('last_name');
            $table->string('email');
            $table->string('phone');

            // Store in cents to avoid float issues
            $table->integer('total_amount');

            $table->string('status')->default('pending'); // pending|succeeded|failed
            $table->string('stripe_checkout_session_id')->nullable()->unique();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};

