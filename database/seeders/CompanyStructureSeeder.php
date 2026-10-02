<?php

namespace Database\Seeders;

use App\Models\AuditLog;
use App\Models\Branch;
use App\Models\Company;
use App\Models\PointOfSale;
use App\Models\Role;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CompanyStructureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Empresa Principal
        $company = Company::firstOrCreate(
            ['cnpj' => '12.345.678/0001-90'],
            [
                'name'       => 'Grupo Supermercados Alvorada Ltda',
                'trade_name' => 'Rede Alvorada Supermercados',
                'ie'         => '112.233.445.566',
                'phone'      => '(11) 3344-5500',
                'email'      => 'contato@alvorada.com.br',
                'status'     => true,
            ]
        );

        // 2. Filiais
        // Filial 1 - Matriz Centro
        $branchCentro = Branch::firstOrCreate(
            ['company_id' => $company->id, 'code' => '001'],
            [
                'name'            => 'Supermercado Alvorada - Loja 01 Centro',
                'cnpj'            => '12.345.678/0001-90',
                'ie'              => '112.233.445.566',
                'phone'           => '(11) 3344-5501',
                'email'           => 'centro@alvorada.com.br',
                'zip_code'        => '01001-000',
                'address'         => 'Praça da Sé',
                'number'          => '100',
                'neighborhood'    => 'Centro Histórico',
                'city'            => 'São Paulo',
                'state'           => 'SP',
                'is_headquarters' => true,
                'status'          => true,
            ]
        );

        // Filial 2 - Loja Sul
        $branchSul = Branch::firstOrCreate(
            ['company_id' => $company->id, 'code' => '002'],
            [
                'name'            => 'Supermercado Alvorada - Loja 02 Zona Sul',
                'cnpj'            => '12.345.678/0002-71',
                'ie'              => '112.233.445.577',
                'phone'           => '(11) 3344-5502',
                'email'           => 'sul@alvorada.com.br',
                'zip_code'        => '04502-000',
                'address'         => 'Av. Santo Amaro',
                'number'          => '2500',
                'neighborhood'    => 'Vila Nova Conceição',
                'city'            => 'São Paulo',
                'state'           => 'SP',
                'is_headquarters' => false,
                'status'          => true,
            ]
        );

        // Filial 3 - Centro de Distribuição
        $branchCD = Branch::firstOrCreate(
            ['company_id' => $company->id, 'code' => '003'],
            [
                'name'            => 'Supermercado Alvorada - Centro de Distribuição (CD)',
                'cnpj'            => '12.345.678/0003-52',
                'ie'              => '112.233.445.588',
                'phone'           => '(11) 3344-5503',
                'email'           => 'cd@alvorada.com.br',
                'zip_code'        => '07700-000',
                'address'         => 'Rodovia Anhanguera',
                'number'          => 'Km 32',
                'neighborhood'    => 'Distrito Industrial',
                'city'            => 'Cajamar',
                'state'           => 'SP',
                'is_headquarters' => false,
                'status'          => true,
            ]
        );

        // 3. Depósitos por Filial
        // Depósitos Loja Centro
        Warehouse::firstOrCreate(
            ['branch_id' => $branchCentro->id, 'code' => 'DEP-01-VND'],
            [
                'name'           => 'Área de Vendas - Loja Centro (Gôndolas)',
                'description'    => 'Piso de loja para atendimento ao cliente e reposição rápida',
                'is_sales_floor' => true,
                'is_default'     => false,
                'status'         => true,
            ]
        );
        Warehouse::firstOrCreate(
            ['branch_id' => $branchCentro->id, 'code' => 'DEP-01-GER'],
            [
                'name'           => 'Depósito Geral Centro (Estoque Superior)',
                'description'    => 'Estoque central e recebimento de mercadorias da filial Centro',
                'is_sales_floor' => false,
                'is_default'     => true,
                'status'         => true,
            ]
        );
        Warehouse::firstOrCreate(
            ['branch_id' => $branchCentro->id, 'code' => 'DEP-01-FRI'],
            [
                'name'           => 'Câmara Frigorífica - Carnes & Laticínios',
                'description'    => 'Ambiente refrigerado e congelado para perecíveis',
                'is_sales_floor' => false,
                'is_default'     => false,
                'status'         => true,
            ]
        );
        Warehouse::firstOrCreate(
            ['branch_id' => $branchCentro->id, 'code' => 'DEP-01-AVA'],
            [
                'name'           => 'Área de Trocas, Perdas & Avarias',
                'description'    => 'Segregação de itens avariados ou com prazo vencido para devolução/descarte',
                'is_sales_floor' => false,
                'is_default'     => false,
                'status'         => true,
            ]
        );

        // Depósitos Loja Sul
        Warehouse::firstOrCreate(
            ['branch_id' => $branchSul->id, 'code' => 'DEP-02-VND'],
            [
                'name'           => 'Área de Vendas - Loja Zona Sul',
                'description'    => 'Gôndolas e frente de loja',
                'is_sales_floor' => true,
                'is_default'     => false,
                'status'         => true,
            ]
        );
        Warehouse::firstOrCreate(
            ['branch_id' => $branchSul->id, 'code' => 'DEP-02-GER'],
            [
                'name'           => 'Depósito Geral Sul',
                'description'    => 'Depósito principal de estoque',
                'is_sales_floor' => false,
                'is_default'     => true,
                'status'         => true,
            ]
        );

        // Depósitos CD Cajamar
        Warehouse::firstOrCreate(
            ['branch_id' => $branchCD->id, 'code' => 'CD-01-SEC'],
            [
                'name'           => 'Galpão A - Alimentos Secos e Mercearia',
                'description'    => 'Armazenamento em paletes de alta densidade',
                'is_sales_floor' => false,
                'is_default'     => true,
                'status'         => true,
            ]
        );
        Warehouse::firstOrCreate(
            ['branch_id' => $branchCD->id, 'code' => 'CD-01-FRI'],
            [
                'name'           => 'Galpão B - Frios e Congelados Industriais',
                'description'    => 'Câmaras frigoríficas industriais',
                'is_sales_floor' => false,
                'is_default'     => false,
                'status'         => true,
            ]
        );

        // 4. PDVs por Filial
        // PDVs Loja Centro
        PointOfSale::firstOrCreate(
            ['branch_id' => $branchCentro->id, 'number' => 1],
            ['name' => 'Caixa 01 - Rápido (até 15 volumes)', 'terminal_code' => 'PDV-01-01', 'ip_address' => '192.168.1.101', 'status' => 'active']
        );
        PointOfSale::firstOrCreate(
            ['branch_id' => $branchCentro->id, 'number' => 2],
            ['name' => 'Caixa 02 - Preferencial', 'terminal_code' => 'PDV-01-02', 'ip_address' => '192.168.1.102', 'status' => 'active']
        );
        PointOfSale::firstOrCreate(
            ['branch_id' => $branchCentro->id, 'number' => 3],
            ['name' => 'Caixa 03 - Geral', 'terminal_code' => 'PDV-01-03', 'ip_address' => '192.168.1.103', 'status' => 'active']
        );
        PointOfSale::firstOrCreate(
            ['branch_id' => $branchCentro->id, 'number' => 4],
            ['name' => 'Caixa 04 - Geral', 'terminal_code' => 'PDV-01-04', 'ip_address' => '192.168.1.104', 'status' => 'active']
        );

        // PDVs Loja Sul
        PointOfSale::firstOrCreate(
            ['branch_id' => $branchSul->id, 'number' => 1],
            ['name' => 'Caixa 01 - Atendimento Geral', 'terminal_code' => 'PDV-02-01', 'ip_address' => '192.168.2.101', 'status' => 'active']
        );
        PointOfSale::firstOrCreate(
            ['branch_id' => $branchSul->id, 'number' => 2],
            ['name' => 'Caixa 02 - Preferencial / Rápido', 'terminal_code' => 'PDV-02-02', 'ip_address' => '192.168.2.102', 'status' => 'active']
        );

        // 5. Usuários com diferentes níveis de acesso e perfis
        $superadminRole    = Role::where('name', Role::SUPERADMIN)->first();
        $adminRole         = Role::where('name', Role::ADMINISTRADOR)->first();
        $gerenteRole       = Role::where('name', Role::GERENTE)->first();
        $operadorRole      = Role::where('name', Role::OPERADOR)->first();
        $auditorRole       = Role::where('name', Role::AUDITOR)->first();

        // 5.1 Superadmin Geral do Sistema
        $superadmin = User::firstOrCreate(
            ['email' => 'superadmin@marketcore.com'],
            [
                'name'          => 'Super Administrador MarketCore',
                'password'      => Hash::make('senha123'),
                'is_superadmin' => true,
                'all_branches'  => true,
                'status'        => 'active',
                'role_id'       => $superadminRole?->id,
            ]
        );

        // 5.2 Administrador da Empresa (Acesso a todas as filiais)
        $adminEmpresa = User::firstOrCreate(
            ['email' => 'admin@alvorada.com'],
            [
                'company_id'    => $company->id,
                'name'          => 'Carlos Eduardo (Diretor Operações)',
                'password'      => Hash::make('senha123'),
                'is_superadmin' => false,
                'all_branches'  => true,
                'status'        => 'active',
                'role_id'       => $adminRole?->id,
            ]
        );

        // 5.3 Gerente Loja Centro (Apenas Filial 1)
        $gerenteCentro = User::firstOrCreate(
            ['email' => 'gerente.centro@alvorada.com'],
            [
                'company_id'    => $company->id,
                'name'          => 'Marcos Vinicius (Gerente Centro)',
                'password'      => Hash::make('senha123'),
                'is_superadmin' => false,
                'all_branches'  => false,
                'status'        => 'active',
                'role_id'       => $gerenteRole?->id,
            ]
        );
        $gerenteCentro->branches()->syncWithoutDetaching([
            $branchCentro->id => ['role_id' => $gerenteRole?->id, 'is_default' => true],
        ]);

        // 5.4 Gerente Loja Sul (Apenas Filial 2)
        $gerenteSul = User::firstOrCreate(
            ['email' => 'gerente.sul@alvorada.com'],
            [
                'company_id'    => $company->id,
                'name'          => 'Juliana Mendes (Gerente Zona Sul)',
                'password'      => Hash::make('senha123'),
                'is_superadmin' => false,
                'all_branches'  => false,
                'status'        => 'active',
                'role_id'       => $gerenteRole?->id,
            ]
        );
        $gerenteSul->branches()->syncWithoutDetaching([
            $branchSul->id => ['role_id' => $gerenteRole?->id, 'is_default' => true],
        ]);

        // 5.5 Operador de Caixa (Apenas Filial 1)
        $operador = User::firstOrCreate(
            ['email' => 'operador@alvorada.com'],
            [
                'company_id'    => $company->id,
                'name'          => 'Fernanda Lima (Operadora Caixa)',
                'password'      => Hash::make('senha123'),
                'is_superadmin' => false,
                'all_branches'  => false,
                'status'        => 'active',
                'role_id'       => $operadorRole?->id,
            ]
        );
        $operador->branches()->syncWithoutDetaching([
            $branchCentro->id => ['role_id' => $operadorRole?->id, 'is_default' => true],
        ]);

        // 5.6 Usuário com Permissões Distintas por Filial (Gerente na Filial 1, Operador na Filial 2)
        $multiRoleUser = User::firstOrCreate(
            ['email' => 'multirole@alvorada.com'],
            [
                'company_id'    => $company->id,
                'name'          => 'Rodrigo Almeida (Gerente Loja 1 / Apoio Loja 2)',
                'password'      => Hash::make('senha123'),
                'is_superadmin' => false,
                'all_branches'  => false,
                'status'        => 'active',
                'role_id'       => $operadorRole?->id, // fallback global
            ]
        );
        $multiRoleUser->branches()->syncWithoutDetaching([
            $branchCentro->id => ['role_id' => $gerenteRole?->id, 'is_default' => true],
            $branchSul->id    => ['role_id' => $operadorRole?->id, 'is_default' => false],
        ]);

        // 5.7 Auditor Geral
        $auditor = User::firstOrCreate(
            ['email' => 'auditor@alvorada.com'],
            [
                'company_id'    => $company->id,
                'name'          => 'Beatriz Santos (Auditora de Conformidade)',
                'password'      => Hash::make('senha123'),
                'is_superadmin' => false,
                'all_branches'  => true,
                'status'        => 'active',
                'role_id'       => $auditorRole?->id,
            ]
        );

        // 6. Registro inicial de auditoria
        AuditLog::create([
            'user_id'     => $superadmin->id,
            'branch_id'   => $branchCentro->id,
            'action'      => 'system_setup',
            'description' => 'Inicialização da estrutura corporativa e perfis do MarketCore ERP',
            'ip_address'  => '127.0.0.1',
            'user_agent'  => 'System Seeder Initializer',
            'created_at'  => now(),
        ]);
    }
}
