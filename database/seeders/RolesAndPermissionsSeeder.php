<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Criação das Permissões Granulares
        $permissions = [
            // Dashboard
            ['name' => 'dashboard.view', 'label' => 'Visualizar Dashboard Gerencial', 'group' => 'Dashboard'],

            // Empresas & Filiais
            ['name' => 'companies.view',   'label' => 'Visualizar Empresas', 'group' => 'Empresas'],
            ['name' => 'companies.manage', 'label' => 'Gerenciar Empresas',  'group' => 'Empresas'],
            ['name' => 'branches.view',    'label' => 'Visualizar Filiais',  'group' => 'Filiais'],
            ['name' => 'branches.manage',  'label' => 'Gerenciar Filiais',   'group' => 'Filiais'],

            // Depósitos & Estoques
            ['name' => 'warehouses.view',   'label' => 'Visualizar Depósitos', 'group' => 'Depósitos'],
            ['name' => 'warehouses.manage', 'label' => 'Gerenciar Depósitos',  'group' => 'Depósitos'],

            // PDVs / Frentes de Caixa
            ['name' => 'pos.view',   'label' => 'Visualizar PDVs', 'group' => 'PDVs'],
            ['name' => 'pos.manage', 'label' => 'Gerenciar PDVs',  'group' => 'PDVs'],

            // Usuários e Acessos
            ['name' => 'users.view',   'label' => 'Visualizar Usuários', 'group' => 'Usuários'],
            ['name' => 'users.manage', 'label' => 'Gerenciar Usuários',  'group' => 'Usuários'],

            // Auditoria
            ['name' => 'audit.view', 'label' => 'Visualizar Logs de Auditoria', 'group' => 'Auditoria'],

            // Catálogo de Produtos, Marcas & Categorias
            ['name' => 'products.view',   'label' => 'Visualizar Produtos', 'group' => 'Produtos'],
            ['name' => 'products.create', 'label' => 'Cadastrar Produtos',  'group' => 'Produtos'],
            ['name' => 'products.edit',   'label' => 'Editar Produtos',     'group' => 'Produtos'],
            ['name' => 'products.delete', 'label' => 'Excluir Produtos',    'group' => 'Produtos'],
            ['name' => 'products.manage', 'label' => 'Gerenciar Produtos',  'group' => 'Produtos'],

            ['name' => 'brands.view',     'label' => 'Visualizar Marcas',   'group' => 'Produtos'],
            ['name' => 'brands.manage',   'label' => 'Gerenciar Marcas',    'group' => 'Produtos'],

            ['name' => 'categories.view',   'label' => 'Visualizar Categorias', 'group' => 'Produtos'],
            ['name' => 'categories.manage', 'label' => 'Gerenciar Categorias',  'group' => 'Produtos'],
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm['name']], $perm);
        }

        // 2. Criação das Roles solicitadas
        $roles = [
            [
                'name'        => Role::SUPERADMIN,
                'label'       => 'Superadministrador',
                'description' => 'Acesso total, irrestrito e global ao sistema e banco de dados.',
                'is_system'   => true,
            ],
            [
                'name'        => Role::ADMINISTRADOR,
                'label'       => 'Administrador Geral',
                'description' => 'Administrador da empresa com controle amplo de cadastros e configurações.',
                'is_system'   => true,
            ],
            [
                'name'        => Role::GERENTE,
                'label'       => 'Gerente de Filial',
                'description' => 'Responsável pelas operações, depósitos, caixas e equipe da filial.',
                'is_system'   => true,
            ],
            [
                'name'        => Role::COMPRADOR,
                'label'       => 'Comprador',
                'description' => 'Responsável por negociações, cotações e compras de suprimentos.',
                'is_system'   => true,
            ],
            [
                'name'        => Role::ESTOQUE,
                'label'       => 'Encarregado de Estoque',
                'description' => 'Responsável por depósitos, recebimentos, movimentações e inventário.',
                'is_system'   => true,
            ],
            [
                'name'        => Role::FISCAL,
                'label'       => 'Fiscal / Contábil',
                'description' => 'Responsável por parametrizações fiscais, tributações e emissões.',
                'is_system'   => true,
            ],
            [
                'name'        => Role::FINANCEIRO,
                'label'       => 'Financeiro',
                'description' => 'Contas a pagar, contas a receber, tesouraria e conciliações.',
                'is_system'   => true,
            ],
            [
                'name'        => Role::OPERADOR,
                'label'       => 'Operador de PDV',
                'description' => 'Operador de caixa no atendimento ao cliente e faturamento.',
                'is_system'   => true,
            ],
            [
                'name'        => Role::AUDITOR,
                'label'       => 'Auditor Interno',
                'description' => 'Acesso para verificação de conformidade, logs, conferência e auditorias.',
                'is_system'   => true,
            ],
        ];

        $roleModels = [];
        foreach ($roles as $roleData) {
            $roleModels[$roleData['name']] = Role::firstOrCreate(['name' => $roleData['name']], $roleData);
        }

        // 3. Atribuição de permissões por perfil
        $allPermissions = Permission::all()->pluck('id')->toArray();

        // Administrador: todas as permissões do sistema
        $roleModels[Role::ADMINISTRADOR]->permissions()->sync($allPermissions);

        // Gerente: dashboard, filiais (view), depósitos (view, manage), pos (view, manage), users (view), audit (view)
        $gerentePerms = Permission::whereIn('name', [
            'dashboard.view', 'branches.view', 'warehouses.view', 'warehouses.manage',
            'pos.view', 'pos.manage', 'users.view', 'audit.view'
        ])->pluck('id')->toArray();
        $roleModels[Role::GERENTE]->permissions()->sync($gerentePerms);

        // Comprador: dashboard, filiais (view), depósitos (view)
        $compradorPerms = Permission::whereIn('name', [
            'dashboard.view', 'branches.view', 'warehouses.view'
        ])->pluck('id')->toArray();
        $roleModels[Role::COMPRADOR]->permissions()->sync($compradorPerms);

        // Estoque: dashboard, depósitos (view, manage)
        $estoquePerms = Permission::whereIn('name', [
            'dashboard.view', 'warehouses.view', 'warehouses.manage'
        ])->pluck('id')->toArray();
        $roleModels[Role::ESTOQUE]->permissions()->sync($estoquePerms);

        // Fiscal: dashboard, filiais (view), audit (view)
        $fiscalPerms = Permission::whereIn('name', [
            'dashboard.view', 'branches.view', 'audit.view'
        ])->pluck('id')->toArray();
        $roleModels[Role::FISCAL]->permissions()->sync($fiscalPerms);

        // Financeiro: dashboard, filiais (view), pos (view), audit (view)
        $financeiroPerms = Permission::whereIn('name', [
            'dashboard.view', 'branches.view', 'pos.view', 'audit.view'
        ])->pluck('id')->toArray();
        $roleModels[Role::FINANCEIRO]->permissions()->sync($financeiroPerms);

        // Operador: dashboard, pos (view)
        $operadorPerms = Permission::whereIn('name', [
            'dashboard.view', 'pos.view'
        ])->pluck('id')->toArray();
        $roleModels[Role::OPERADOR]->permissions()->sync($operadorPerms);

        // Auditor: todas para leitura + auditoria
        $auditorPerms = Permission::whereIn('name', [
            'dashboard.view', 'companies.view', 'branches.view', 'warehouses.view',
            'pos.view', 'users.view', 'audit.view'
        ])->pluck('id')->toArray();
        $roleModels[Role::AUDITOR]->permissions()->sync($auditorPerms);
    }
}
