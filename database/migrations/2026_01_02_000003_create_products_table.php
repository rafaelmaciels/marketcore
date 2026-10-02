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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();
            $table->string('sku', 50)->unique();
            $table->string('internal_code', 50)->nullable()->index();
            $table->string('name');
            $table->string('short_description', 500)->nullable();
            $table->text('description')->nullable();

            // Relacionamentos com Marcas e Categorias
            $table->foreignId('brand_id')->nullable()->constrained('brands')->nullOnDelete();
            $table->foreignId('department_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->foreignId('subcategory_id')->nullable()->constrained('categories')->nullOnDelete();

            // Parâmetros operacionais
            $table->string('unit_of_measure', 10)->default('UN')->comment('UN, KG, LT, CX, etc.');
            $table->boolean('is_weighable')->default(false)->comment('Produto pesável / balança');
            $table->string('status', 20)->default('active')->comment('active, inactive, discontinued');

            // Fiscal
            $table->string('ncm', 10)->nullable();
            $table->string('cest', 10)->nullable();
            $table->string('origin', 2)->default('0')->comment('0-Nacional, 1-Estrangeira Direta, etc.');
            $table->string('cst_csosn', 10)->nullable();
            $table->string('cfop_default', 10)->nullable()->default('5102');

            // Dimensões & Capacidade
            $table->decimal('net_weight', 10, 3)->nullable()->comment('Peso líquido em kg');
            $table->decimal('gross_weight', 10, 3)->nullable()->comment('Peso bruto em kg');
            $table->string('volume', 50)->nullable()->comment('ex: 350ml, 2L');

            // Parâmetros teóricos de estoque (sem preços ou estoque físico)
            $table->decimal('minimum_stock', 12, 3)->default(0);
            $table->decimal('maximum_stock', 12, 3)->default(0);
            $table->decimal('safety_stock', 12, 3)->default(0);

            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'status']);
            $table->index('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
