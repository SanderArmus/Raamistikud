<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('product_id');

            // Snapshot fields (so historical order data doesn't change)
            $table->string('name_snapshot');
            $table->integer('unit_price_snapshot');

            $table->integer('quantity');

            $table->timestamps();
        });

        // Helpful indexes for querying
        DB::statement('CREATE INDEX order_items_order_id_index ON order_items(order_id);');
        DB::statement('CREATE INDEX order_items_product_id_index ON order_items(product_id);');
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};

