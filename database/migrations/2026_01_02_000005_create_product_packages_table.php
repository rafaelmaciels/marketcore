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
        Schema::create('product_packages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->string('name')->comment('Ex: Unidade, Pacote c/ 6, Caixa c/ 24');
            $table->string('unit_type', 10)->default('UN')->comment('UN, PCT, CX, FD, DP, etc.');
            $table->decimal('conversion_factor', 12, 4)->default(1.0000)->comment('Multiplicador em relação à unidade básica (ex: 24)');
            $table->string('barcode', 50)->nullable()->comment('DUN-14 ou código de barras da caixa');
            $table->boolean('is_default_sale')->default(false)->comment('Embalagem padrão no checkout');
            $table->boolean('status')->default(true);
            $table->timestamps();

            $table->index(['product_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_packages');
    }
};
