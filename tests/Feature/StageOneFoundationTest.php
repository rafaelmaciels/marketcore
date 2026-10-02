<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Role;
use App\Models\User;
use App\Models\Warehouse;
use Database\Seeders\CompanyStructureSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StageOneFoundationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Executa seeders estruturais
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(CompanyStructureSeeder::class);
    }

    /**
     * Teste 1: Login bem-sucedido e registro na trilha de auditoria.
     */
    public function test_login_successful_and_audit_logged(): void
    {
        $response = $this->post('/login', [
            'email'    => 'admin@alvorada.com',
            'password' => 'senha123',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticated();

        // Verifica que a filial ativa foi definida na sessão
        $this->assertNotNull(session('active_branch_id'));

        // Verifica que o log de auditoria foi gravado
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'login',
        ]);
    }

    /**
     * Teste 2: Falha de login com credenciais incorretas e auditoria de login_failed.
     */
    public function test_login_fails_with_invalid_password(): void
    {
        $response = $this->post('/login', [
            'email'    => 'admin@alvorada.com',
            'password' => 'senha_errada',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'login_failed',
        ]);
    }

    /**
     * Teste 3: Isolamento entre filiais - Gerente da Loja 1 NÃO pode alternar para a Loja 2.
     */
    public function test_branch_isolation_unauthorized_switch_is_blocked(): void
    {
        $gerenteCentro = User::where('email', 'gerente.centro@alvorada.com')->first();
        $filialSul = Branch::where('code', '002')->first();

        // Loga com o Gerente do Centro
        $this->actingAs($gerenteCentro);

        // Tenta forçar a troca para a filial Sul
        $response = $this->post('/branch/switch', [
            'branch_id' => $filialSul->id,
        ]);

        // Deve retornar 403 Forbidden
        $response->assertStatus(403);

        // Deve ter registrado tentativa não autorizada no audit log
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'unauthorized_branch_switch',
        ]);
    }

    /**
     * Teste 4: Usuário com perfil e permissões diferentes dependendo da filial (Multirole).
     */
    public function test_user_with_different_roles_per_branch(): void
    {
        $multiUser = User::where('email', 'multirole@alvorada.com')->first();
        $branchCentro = Branch::where('code', '001')->first();
        $branchSul = Branch::where('code', '002')->first();

        // Na Filial Centro, ele é Gerente
        $roleCentro = $multiUser->getRoleForBranch($branchCentro->id);
        $this->assertEquals(Role::GERENTE, $roleCentro->name);
        $this->assertTrue($multiUser->hasPermission('warehouses.manage', $branchCentro->id));

        // Na Filial Sul, ele é Operador de Caixa
        $roleSul = $multiUser->getRoleForBranch($branchSul->id);
        $this->assertEquals(Role::OPERADOR, $roleSul->name);
        // Operador NÃO tem permissão de gerenciar depósitos
        $this->assertFalse($multiUser->hasPermission('warehouses.manage', $branchSul->id));
        // Operador tem permissão de visualizar PDVs
        $this->assertTrue($multiUser->hasPermission('pos.view', $branchSul->id));
    }

    /**
     * Teste 5: Superadmin tem acesso irrestrito global a qualquer filial.
     */
    public function test_superadmin_has_unrestricted_access_to_all_branches(): void
    {
        $superadmin = User::where('email', 'superadmin@marketcore.com')->first();
        $branchSul = Branch::where('code', '002')->first();

        $this->actingAs($superadmin);

        // Superadmin pode trocar para qualquer filial
        $response = $this->post('/branch/switch', [
            'branch_id' => $branchSul->id,
        ]);

        $response->assertRedirect();
        $this->assertEquals($branchSul->id, session('active_branch_id'));

        // Superadmin tem permissão total
        $this->assertTrue($superadmin->hasPermission('branches.manage', $branchSul->id));
        $this->assertTrue($superadmin->hasPermission('users.manage', $branchSul->id));
    }

    /**
     * Teste 6: Criação de depósito registra log de auditoria com filial correta.
     */
    public function test_creating_warehouse_generates_audit_log_with_active_branch(): void
    {
        $gerenteCentro = User::where('email', 'gerente.centro@alvorada.com')->first();
        $branchCentro = Branch::where('code', '001')->first();

        $this->actingAs($gerenteCentro)
            ->withSession(['active_branch_id' => $branchCentro->id]);

        $response = $this->post('/warehouses', [
            'name'           => 'Câmara de Hortifruti Teste',
            'code'           => 'DEP-TEST-01',
            'description'    => 'Depósito para testes automatizados',
            'is_sales_floor' => false,
            'is_default'     => false,
            'status'         => true,
        ]);

        $response->assertRedirect('/warehouses');

        $this->assertDatabaseHas('warehouses', [
            'name'      => 'Câmara de Hortifruti Teste',
            'branch_id' => $branchCentro->id,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action'    => 'create',
            'branch_id' => $branchCentro->id,
        ]);
    }
}
