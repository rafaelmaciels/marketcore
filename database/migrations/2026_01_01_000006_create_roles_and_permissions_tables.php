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
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique()->comment('Slug do perfil, ex: gerente, operador, etc.');
            $table->string('label')->comment('Nome exibido, ex: Gerente de Filial');
            $table->text('description')->nullable();
            $table->boolean('is_system')->default(false)->comment('Se é perfil padrão do sistema');
            $table->timestamps();
        });

        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique()->comment('Ex: branches.view, users.manage');
            $table->string('label')->comment('Nome legível');
            $table->string('group', 50)->comment('Módulo/agrupador da permissão');
            $table->timestamps();
        });

        Schema::create('role_permission', function (Blueprint $table) {
            $table->foreignId('role_id')->constrained('roles')->onDelete('cascade');
            $table->foreignId('permission_id')->constrained('permissions')->onDelete('cascade');
            $table->primary(['role_id', 'permission_id']);
        });

        // Adiciona role_id global opcional na tabela users
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('role_id')->nullable()->after('company_id')->constrained('roles')->nullOnDelete();
        });

        // Vinculação de usuário com filiais, permitindo perfis específicos por filial
        Schema::create('branch_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('branch_id')->constrained('branches')->onDelete('cascade');
            $table->foreignId('role_id')->nullable()->constrained('roles')->onDelete('set null')->comment('Perfil específico nesta filial');
            $table->boolean('is_default')->default(false)->comment('Filial preferencial do usuário');
            $table->timestamps();

            $table->unique(['user_id', 'branch_id']);
            $table->index(['user_id', 'branch_id', 'role_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('branch_user');
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
            $table->dropColumn('role_id');
        });
        Schema::dropIfExists('role_permission');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');
    }
};
