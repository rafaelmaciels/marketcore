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
        Schema::create('product_barcodes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->string('barcode', 50)->index();
            $table->string('type', 20)->default('ean13')->comment('ean13, ean8, gtin, internal');
            $table->boolean('is_main')->default(false)->comment('Código principal para impressão/gôndola');
            $table->timestamps();

            $table->index(['product_id', 'is_main']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_barcodes');
    }
};
