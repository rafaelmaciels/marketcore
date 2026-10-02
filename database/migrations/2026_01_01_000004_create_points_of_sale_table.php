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
        Schema::create('points_of_sale', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->onDelete('cascade');
            $table->unsignedSmallInteger('number')->comment('Número do Caixa');
            $table->string('name');
            $table->string('terminal_code', 50)->nullable()->comment('Identificador de hardware/estação');
            $table->string('ip_address', 45)->nullable();
            $table->string('status', 20)->default('active')->comment('active, inactive, maintenance');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['branch_id', 'number']);
            $table->index(['branch_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('points_of_sale');
    }
};
