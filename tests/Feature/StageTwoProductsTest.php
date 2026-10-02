<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Company;
use App\Models\Product;
use App\Models\ProductBarcode;
use App\Models\ProductPackage;
use App\Models\User;
use Database\Seeders\CompanyStructureSeeder;
use Database\Seeders\ProductsSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StageTwoProductsTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected Company $company;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(CompanyStructureSeeder::class);
        $this->seed(ProductsSeeder::class);

        $this->adminUser = User::where('email', 'admin@alvorada.com')->first();
        $this->company = Company::first();

        // Login como Administrador
        $this->actingAs($this->adminUser);
        $branch = \App\Models\Branch::first();
        session(['active_branch_id' => $branch ? $branch->id : 1]);
    }

    /**
     * Teste 1: Hierarquia Mercadológica (Departamento -> Categoria -> Subcategoria).
     */
    public function test_category_hierarchy_creation_and_relationships(): void
    {
        // 1. Criar Departamento (Nível 1)
        $deptResponse = $this->post(route('categories.store'), [
            'name'        => 'Padaria & Confeitaria',
            'code'        => 'DEP-PAD',
            'level'       => Category::LEVEL_DEPARTMENT,
            'description' => 'Pães, bolos e salgados',
            'status'      => 1,
        ]);
        $deptResponse->assertRedirect();
        $dept = Category::where('code', 'DEP-PAD')->first();
        $this->assertNotNull($dept);
        $this->assertNull($dept->parent_id);
        $this->assertTrue($dept->isDepartment());

        // 2. Criar Categoria (Nível 2) vinculada ao Departamento
        $catResponse = $this->post(route('categories.store'), [
            'name'      => 'Pães Especiais',
            'code'      => 'CAT-PAO-ESP',
            'level'     => Category::LEVEL_CATEGORY,
            'parent_id' => $dept->id,
            'status'    => 1,
        ]);
        $catResponse->assertRedirect();
        $cat = Category::where('code', 'CAT-PAO-ESP')->first();
        $this->assertNotNull($cat);
        $this->assertEquals($dept->id, $cat->parent_id);
        $this->assertTrue($cat->isCategory());

        // 3. Criar Subcategoria (Nível 3) vinculada à Categoria
        $subcatResponse = $this->post(route('categories.store'), [
            'name'      => 'Pão Australiano',
            'code'      => 'SUB-PAO-AUST',
            'level'     => Category::LEVEL_SUBCATEGORY,
            'parent_id' => $cat->id,
            'status'    => 1,
        ]);
        $subcatResponse->assertRedirect();
        $subcat = Category::where('code', 'SUB-PAO-AUST')->first();
        $this->assertNotNull($subcat);
        $this->assertEquals($cat->id, $subcat->parent_id);
        $this->assertTrue($subcat->isSubcategory());

        // Validar navegação na árvore de relacionamentos
        $this->assertEquals($cat->id, $subcat->parent->id);
        $this->assertEquals($dept->id, $cat->parent->id);
        $this->assertTrue($dept->children->contains($cat));
        $this->assertTrue($cat->children->contains($subcat));
    }

    /**
     * Teste 2: CRUD de Marcas.
     */
    public function test_brands_crud_operations(): void
    {
        // Index
        $indexResponse = $this->get(route('brands.index'));
        $indexResponse->assertOk();
        $indexResponse->assertSee('Coca-Cola');

        // Store
        $createResponse = $this->post(route('brands.store'), [
            'name'        => 'Nestlé Chocolates',
            'code'        => 'NEST-CHOC',
            'description' => 'Linha especial de chocolates ao leite',
            'status'      => 1,
        ]);
        $createResponse->assertRedirect();
        $brand = Brand::where('code', 'NEST-CHOC')->first();
        $this->assertNotNull($brand);
        $this->assertEquals('Nestlé Chocolates', $brand->name);

        // Update
        $updateResponse = $this->put(route('brands.update', $brand), [
            'name'        => 'Nestlé Confeitaria',
            'code'        => 'NEST-CONF',
            'description' => 'Chocolates e coberturas',
            'status'      => 1,
        ]);
        $updateResponse->assertRedirect();
        $this->assertEquals('Nestlé Confeitaria', $brand->fresh()->name);

        // Delete (Soft Delete)
        $deleteResponse = $this->delete(route('brands.destroy', $brand));
        $deleteResponse->assertRedirect();
        $this->assertSoftDeleted('brands', ['id' => $brand->id]);
    }

    /**
     * Teste 3: Cadastro de Produto com Múltiplos Códigos de Barras e Embalagens.
     */
    public function test_product_store_with_multiple_barcodes_and_packages(): void
    {
        $brand = Brand::first();
        $dept = Category::departments()->first();
        $cat = Category::categories()->first();

        $productData = [
            'sku'               => 'TEST-GUARANA-2L',
            'internal_code'     => '9901',
            'name'              => 'Refrigerante Guaraná Antarctica 2L',
            'short_description' => 'REFRIG GUARANA 2L',
            'description'       => 'Refrigerante sabor guaraná natural.',
            'brand_id'          => $brand->id,
            'department_id'     => $dept->id,
            'category_id'       => $cat->id,
            'unit_of_measure'   => 'UN',
            'is_weighable'      => 0,
            'status'            => 'active',
            'ncm'               => '2202.10.00',
            'cest'              => '03.011.00',
            'origin'            => '0',
            'cst_csosn'         => '0102',
            'cfop_default'      => '5405',
            'net_weight'        => 2.000,
            'gross_weight'      => 2.050,
            'volume'            => '2L',
            'minimum_stock'     => 10,
            'maximum_stock'     => 100,
            'safety_stock'      => 5,
            'barcodes'          => [
                [
                    'barcode' => '7891991000858',
                    'type'    => 'ean13',
                    'is_main' => 1,
                ],
                [
                    'barcode' => '17891991000855',
                    'type'    => 'gtin',
                    'is_main' => 0,
                ],
                [
                    'barcode' => '9901',
                    'type'    => 'internal',
                    'is_main' => 0,
                ],
            ],
            'packages'          => [
                [
                    'name'              => '1 Garrafa (Unidade)',
                    'unit_type'         => 'UN',
                    'conversion_factor' => 1.0,
                    'barcode'           => '7891991000858',
                    'is_default_sale'   => 1,
                ],
                [
                    'name'              => 'Fardo c/ 6 Garrafas',
                    'unit_type'         => 'FD',
                    'conversion_factor' => 6.0,
                    'barcode'           => '17891991000855',
                    'is_default_sale'   => 0,
                ],
                [
                    'name'              => 'Caixa c/ 24 Garrafas',
                    'unit_type'         => 'CX',
                    'conversion_factor' => 24.0,
                    'barcode'           => '27891991000852',
                    'is_default_sale'   => 0,
                ],
            ],
        ];

        $response = $this->post(route('products.store'), $productData);
        $response->assertRedirect();

        $product = Product::where('sku', 'TEST-GUARANA-2L')->first();
        $this->assertNotNull($product);
        $this->assertEquals('Refrigerante Guaraná Antarctica 2L', $product->name);

        // Valida Códigos de Barras
        $this->assertCount(3, $product->barcodes);
        $this->assertEquals('7891991000858', $product->mainBarcode->barcode);
        $this->assertTrue($product->barcodes->contains('barcode', '17891991000855'));
        $this->assertTrue($product->barcodes->contains('barcode', '9901'));

        // Valida Embalagens
        $this->assertCount(3, $product->packages);
        $this->assertEquals('1 Garrafa (Unidade)', $product->defaultPackage->name);
        $this->assertTrue($product->packages->contains('conversion_factor', 6.0));
        $this->assertTrue($product->packages->contains('conversion_factor', 24.0));

        // Valida registro na auditoria
        $this->assertDatabaseHas('audit_logs', [
            'action'         => 'create',
            'auditable_type' => Product::class,
            'auditable_id'   => $product->id,
        ]);
    }

    /**
     * Teste 4: Conversão de Embalagens e Unidades.
     * Exemplo do prompt:
     * Produto: Refrigerante
     * Unidade: 1 garrafa
     * Pacote: 6 unidades
     * Caixa: 24 unidades
     * 1 caixa = 24 unidades
     */
    public function test_package_unit_conversion_calculations(): void
    {
        $coca = Product::where('sku', 'BEB-COCA-2L')->first();
        $this->assertNotNull($coca);

        $pkgUnit = $coca->packages()->where('conversion_factor', 1.0)->first();
        $pkgPack = $coca->packages()->where('conversion_factor', 6.0)->first();
        $pkgBox  = $coca->packages()->where('conversion_factor', 24.0)->first();

        $this->assertNotNull($pkgUnit);
        $this->assertNotNull($pkgPack);
        $this->assertNotNull($pkgBox);

        // 1 Caixa = 24 unidades
        $this->assertEquals(24.0, $pkgBox->toBaseUnits(1));

        // 2 Caixas = 48 unidades
        $this->assertEquals(48.0, $pkgBox->toBaseUnits(2));

        // 24 unidades = 1 Caixa
        $this->assertEquals(1.0, $pkgBox->fromBaseUnits(24));

        // 48 unidades = 2 Caixas
        $this->assertEquals(2.0, $pkgBox->fromBaseUnits(48));

        // 1 Pacote = 6 unidades
        $this->assertEquals(6.0, $pkgPack->toBaseUnits(1));

        // 2 Pacotes = 12 unidades
        $this->assertEquals(12.0, $pkgPack->toBaseUnits(2));

        // Conversão direta entre embalagens: 1 Caixa para Pacotes = 24 / 6 = 4 Pacotes
        $packsInOneBox = $coca->convertQuantity(1, $pkgBox, $pkgPack);
        $this->assertEquals(4.0, $packsInOneBox);

        // 2 Caixas para Pacotes = 48 / 6 = 8 Pacotes
        $packsInTwoBoxes = $coca->convertQuantity(2, $pkgBox, $pkgPack);
        $this->assertEquals(8.0, $packsInTwoBoxes);
    }

    /**
     * Teste 5: Busca e Filtros de Produtos no Controller.
     */
    public function test_product_search_and_filters(): void
    {
        // 1. Busca por nome
        $resName = $this->get(route('products.index', ['search' => 'Heineken']));
        $resName->assertOk();
        $resName->assertSee('BEB-HEIN-LN330');
        $resName->assertDontSee('CAR-PICANHA-KG');

        // 2. Busca por código de barras principal (EAN-13)
        $resBarcode = $this->get(route('products.index', ['search' => '7894900010015']));
        $resBarcode->assertOk();
        $resBarcode->assertSee('BEB-COCA-2L');
        $resBarcode->assertDontSee('BEB-HEIN-LN330');

        // 3. Filtro por produto pesável (Balança)
        $resWeighable = $this->get(route('products.index', ['is_weighable' => '1']));
        $resWeighable->assertOk();
        $resWeighable->assertSee('CAR-PICANHA-KG');
        $resWeighable->assertDontSee('BEB-COCA-2L');
    }

    /**
     * Teste 6: Visualização da Página de Detalhes com Histórico de Auditoria.
     */
    public function test_product_show_displays_specifications_and_history(): void
    {
        $coca = Product::where('sku', 'BEB-COCA-2L')->first();

        $response = $this->get(route('products.show', $coca));
        $response->assertOk();
        $response->assertSee($coca->name);
        $response->assertSee('7894900010015');
        $response->assertSee('24'); // Fator caixa
        $response->assertSee('Histórico de Alterações');
    }

    /**
     * Teste 7: Edição e Registro de Diff na Auditoria.
     */
    public function test_product_update_records_audit_trail_diff(): void
    {
        $coca = Product::where('sku', 'BEB-COCA-2L')->first();

        $response = $this->put(route('products.update', $coca), [
            'sku'               => $coca->sku,
            'name'              => 'Refrigerante Coca-Cola PET 2L Sabor Original',
            'unit_of_measure'   => 'UN',
            'status'            => 'active',
            'minimum_stock'     => 60,
            'maximum_stock'     => 600,
            'safety_stock'      => 25,
            'barcodes'          => [
                [
                    'barcode' => '7894900010015',
                    'type'    => 'ean13',
                    'is_main' => 1,
                ],
            ],
            'packages'          => [
                [
                    'name'              => 'Garrafa 2L',
                    'unit_type'         => 'UN',
                    'conversion_factor' => 1.0,
                    'is_default_sale'   => 1,
                ],
            ],
        ]);

        $response->assertRedirect(route('products.show', $coca));
        $this->assertEquals('Refrigerante Coca-Cola PET 2L Sabor Original', $coca->fresh()->name);

        // Verifica auditoria de update
        $this->assertDatabaseHas('audit_logs', [
            'action'         => 'update',
            'auditable_type' => Product::class,
            'auditable_id'   => $coca->id,
        ]);
    }
}
