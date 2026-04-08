<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('my_favorite_subjects', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('image')->nullable();
            $table->text('description');

            // Theme-specific fields (movies)
            $table->string('director');
            $table->unsignedSmallInteger('release_year');
            $table->string('genre')->nullable();

            $table->timestamps();

            $table->index(['release_year']);
            $table->index(['director']);
            $table->index(['genre']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('my_favorite_subjects');
    }
};

