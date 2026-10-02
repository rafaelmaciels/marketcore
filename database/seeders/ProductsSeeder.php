<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Company;
use App\Models\Product;
use App\Models\ProductBarcode;
use App\Models\ProductPackage;
use Illuminate\Database\Seeder;

class ProductsSeeder extends Seeder
{
    /**
     * Seed realistic supermarket categories, brands, products, barcodes and packages.
     */
    public function run(): void
    {
        $company = Company::first();
        $companyId = $company ? $company->id : 1;

        // 1. MARCAS
        $brandData = [
            ['name' => 'Coca-Cola', 'code' => 'COCA', 'description' => 'The Coca-Cola Company - Líder mundial em refrigerantes e bebidas não alcoólicas.'],
            ['name' => 'Ambev', 'code' => 'AMBEV', 'description' => 'Companhia de Bebidas das Américas - Cervejas, chás e refrigerantes.'],
            ['name' => 'Heineken', 'code' => 'HEIN', 'description' => 'Cervejaria internacional de puro malte.'],
            ['name' => 'Friboi', 'code' => 'FRIB', 'description' => 'Líder nacional em carnes bovinas in natura e cortes nobres.'],
            ['name' => 'Camil', 'code' => 'CAMIL', 'description' => 'Arroz, feijão e grãos nobres selecionados.'],
            ['name' => 'Nestlé', 'code' => 'NEST', 'description' => 'Chocolates, biscoitos, laticínios e cereais.'],
            ['name' => 'Omo', 'code' => 'OMO', 'description' => 'Unilever - Sabão em pó e líquido de alta performance.'],
            ['name' => 'Ypê', 'code' => 'YPE', 'description' => 'Detergentes, sabões e produtos de limpeza para o lar.'],
        ];

        $brands = [];
        foreach ($brandData as $b) {
            $brands[strtolower($b['code'])] = Brand::firstOrCreate(
                ['code' => $b['code'], 'company_id' => $companyId],
                array_merge($b, ['company_id' => $companyId, 'status' => true])
            );
        }

        // 2. HIERARQUIA MERCADOLÓGICA (DEPARTAMENTO -> CATEGORIA -> SUBCATEGORIA)
        // 2.1 Bebidas
        $depBebidas = Category::firstOrCreate(
            ['code' => 'DEP-BEB', 'company_id' => $companyId],
            ['name' => 'Bebidas', 'level' => Category::LEVEL_DEPARTMENT, 'description' => 'Refrigerantes, cervejas, sucos e vinhos', 'status' => true]
        );

        $catRefrigerantes = Category::firstOrCreate(
            ['code' => 'CAT-REF', 'company_id' => $companyId],
            ['name' => 'Refrigerantes', 'level' => Category::LEVEL_CATEGORY, 'parent_id' => $depBebidas->id, 'status' => true]
        );
        $subcatPet = Category::firstOrCreate(
            ['code' => 'SUB-REF-PET', 'company_id' => $companyId],
            ['name' => 'Garrafas PET 2L', 'level' => Category::LEVEL_SUBCATEGORY, 'parent_id' => $catRefrigerantes->id, 'status' => true]
        );

        $catCervejas = Category::firstOrCreate(
            ['code' => 'CAT-CERV', 'company_id' => $companyId],
            ['name' => 'Cervejas', 'level' => Category::LEVEL_CATEGORY, 'parent_id' => $depBebidas->id, 'status' => true]
        );
        $subcatPuroMalte = Category::firstOrCreate(
            ['code' => 'SUB-CERV-PM', 'company_id' => $companyId],
            ['name' => 'Cervejas Puro Malte', 'level' => Category::LEVEL_SUBCATEGORY, 'parent_id' => $catCervejas->id, 'status' => true]
        );

        // 2.2 Carnes & Açougue
        $depAcougue = Category::firstOrCreate(
            ['code' => 'DEP-ACOU', 'company_id' => $companyId],
            ['name' => 'Açougue & Carnes', 'level' => Category::LEVEL_DEPARTMENT, 'description' => 'Cortes bovinos, suínos e aves com controle de pesagem', 'status' => true]
        );

        $catBovinos = Category::firstOrCreate(
            ['code' => 'CAT-BOV', 'company_id' => $companyId],
            ['name' => 'Carnes Bovinas', 'level' => Category::LEVEL_CATEGORY, 'parent_id' => $depAcougue->id, 'status' => true]
        );
        $subcatNobres = Category::firstOrCreate(
            ['code' => 'SUB-BOV-NOB', 'company_id' => $companyId],
            ['name' => 'Cortes Nobres & Grelha', 'level' => Category::LEVEL_SUBCATEGORY, 'parent_id' => $catBovinos->id, 'status' => true]
        );

        // 2.3 Mercearia
        $depMercearia = Category::firstOrCreate(
            ['code' => 'DEP-MERC', 'company_id' => $companyId],
            ['name' => 'Mercearia de Alimentos', 'level' => Category::LEVEL_DEPARTMENT, 'description' => 'Grãos, massas e matinais', 'status' => true]
        );

        $catGraos = Category::firstOrCreate(
            ['code' => 'CAT-GRAOS', 'company_id' => $companyId],
            ['name' => 'Arroz e Grãos', 'level' => Category::LEVEL_CATEGORY, 'parent_id' => $depMercearia->id, 'status' => true]
        );
        $subcatArroz = Category::firstOrCreate(
            ['code' => 'SUB-ARR-BRANCO', 'company_id' => $companyId],
            ['name' => 'Arroz Branco Tipo 1', 'level' => Category::LEVEL_SUBCATEGORY, 'parent_id' => $catGraos->id, 'status' => true]
        );

        // 2.4 Limpeza & Higiene
        $depLimpeza = Category::firstOrCreate(
            ['code' => 'DEP-LIMP', 'company_id' => $companyId],
            ['name' => 'Limpeza & Lavanderia', 'level' => Category::LEVEL_DEPARTMENT, 'description' => 'Cuidados com a roupa e a casa', 'status' => true]
        );

        $catLavaRoupas = Category::firstOrCreate(
            ['code' => 'CAT-LAV-ROUPAS', 'company_id' => $companyId],
            ['name' => 'Lava Roupas', 'level' => Category::LEVEL_CATEGORY, 'parent_id' => $depLimpeza->id, 'status' => true]
        );
        $subcatPo = Category::firstOrCreate(
            ['code' => 'SUB-SABAO-PO', 'company_id' => $companyId],
            ['name' => 'Sabão em Pó', 'level' => Category::LEVEL_SUBCATEGORY, 'parent_id' => $catLavaRoupas->id, 'status' => true]
        );

        // 3. PRODUTOS COMPLETOS COM CÓDIGOS DE BARRAS E EMBALAGENS DE CONVERSÃO

        // 3.1 Coca-Cola 2L (Exemplo: UN, PCT c/ 6, CX c/ 24)
        $coca = Product::firstOrCreate(
            ['sku' => 'BEB-COCA-2L', 'company_id' => $companyId],
            [
                'internal_code' => '1001',
                'name' => 'Refrigerante Coca-Cola Garrafa PET 2 Litros',
                'short_description' => 'REFRIG COCA-COLA PET 2L',
                'description' => 'Refrigerante com aroma natural de noz-de-cola. Embalagem PET de 2 litros.',
                'brand_id' => $brands['coca']->id,
                'department_id' => $depBebidas->id,
                'category_id' => $catRefrigerantes->id,
                'subcategory_id' => $subcatPet->id,
                'unit_of_measure' => 'UN',
                'is_weighable' => false,
                'status' => 'active',
                'ncm' => '2202.10.00',
                'cest' => '03.011.00',
                'origin' => '0',
                'cst_csosn' => '0102',
                'cfop_default' => '5405',
                'net_weight' => 2.000,
                'gross_weight' => 2.050,
                'volume' => '2L',
                'minimum_stock' => 50,
                'maximum_stock' => 500,
                'safety_stock' => 20,
            ]
        );

        // Barcodes Coca-Cola
        $coca->barcodes()->delete();
        $coca->barcodes()->createMany([
            ['barcode' => '7894900010015', 'type' => ProductBarcode::TYPE_EAN13, 'is_main' => true],
            ['barcode' => '17894900010012', 'type' => ProductBarcode::TYPE_GTIN, 'is_main' => false],
            ['barcode' => '1001', 'type' => ProductBarcode::TYPE_INTERNAL, 'is_main' => false],
        ]);

        // Packages Coca-Cola: 1 garrafa (UN), 1 fardo (6 UN), 1 caixa (24 UN)
        $coca->packages()->delete();
        $coca->packages()->createMany([
            [
                'name' => '1 Garrafa (Unidade)',
                'unit_type' => 'UN',
                'conversion_factor' => 1.0,
                'barcode' => '7894900010015',
                'is_default_sale' => true,
                'status' => true,
            ],
            [
                'name' => 'Pacote / Fardo c/ 6 unidades',
                'unit_type' => 'PCT',
                'conversion_factor' => 6.0,
                'barcode' => '17894900010012',
                'is_default_sale' => false,
                'status' => true,
            ],
            [
                'name' => 'Caixa Distribuição c/ 24 unidades',
                'unit_type' => 'CX',
                'conversion_factor' => 24.0,
                'barcode' => '27894900010019',
                'is_default_sale' => false,
                'status' => true,
            ],
        ]);

        // 3.2 Cerveja Heineken Long Neck 330ml
        $heineken = Product::firstOrCreate(
            ['sku' => 'BEB-HEIN-LN330', 'company_id' => $companyId],
            [
                'internal_code' => '1002',
                'name' => 'Cerveja Heineken Puro Malte Garrafa Long Neck 330ml',
                'short_description' => 'CERV HEINEKEN LN 330ML',
                'description' => 'Cerveja Lager Puro Malte de baixa fermentação.',
                'brand_id' => $brands['hein']->id,
                'department_id' => $depBebidas->id,
                'category_id' => $catCervejas->id,
                'subcategory_id' => $subcatPuroMalte->id,
                'unit_of_measure' => 'UN',
                'is_weighable' => false,
                'status' => 'active',
                'ncm' => '2203.00.00',
                'cest' => '03.001.00',
                'origin' => '0',
                'cst_csosn' => '0102',
                'cfop_default' => '5405',
                'net_weight' => 0.330,
                'gross_weight' => 0.580,
                'volume' => '330ml',
                'minimum_stock' => 120,
                'maximum_stock' => 1200,
                'safety_stock' => 60,
            ]
        );

        $heineken->barcodes()->delete();
        $heineken->barcodes()->createMany([
            ['barcode' => '7896045505018', 'type' => ProductBarcode::TYPE_EAN13, 'is_main' => true],
            ['barcode' => '17896045505015', 'type' => ProductBarcode::TYPE_GTIN, 'is_main' => false],
        ]);

        $heineken->packages()->delete();
        $heineken->packages()->createMany([
            ['name' => 'Garrafa Individual', 'unit_type' => 'UN', 'conversion_factor' => 1.0, 'barcode' => '7896045505018', 'is_default_sale' => true, 'status' => true],
            ['name' => 'Six Pack (6 unidades)', 'unit_type' => 'PCT', 'conversion_factor' => 6.0, 'barcode' => '17896045505015', 'is_default_sale' => false, 'status' => true],
            ['name' => 'Caixa Fechada c/ 24 unidades', 'unit_type' => 'CX', 'conversion_factor' => 24.0, 'barcode' => '27896045505012', 'is_default_sale' => false, 'status' => true],
        ]);

        // 3.3 Picanha Bovina Resfriada (Produto Pesável de Balança)
        $picanha = Product::firstOrCreate(
            ['sku' => 'CAR-PICANHA-KG', 'company_id' => $companyId],
            [
                'internal_code' => '2001',
                'name' => 'Picanha Bovina Resfriada Peça / Kg Friboi',
                'short_description' => 'PICANHA RESFR BOV KG',
                'description' => 'Corte nobre bovino com capa de gordura uniforme, ideal para churrasco.',
                'brand_id' => $brands['frib']->id,
                'department_id' => $depAcougue->id,
                'category_id' => $catBovinos->id,
                'subcategory_id' => $subcatNobres->id,
                'unit_of_measure' => 'KG',
                'is_weighable' => true, // BALANÇA
                'status' => 'active',
                'ncm' => '0201.20.90',
                'cest' => '17.087.00',
                'origin' => '0',
                'cst_csosn' => '0102',
                'cfop_default' => '5102',
                'net_weight' => 1.000,
                'gross_weight' => 1.020,
                'volume' => 'Peça vácuo',
                'minimum_stock' => 15,
                'maximum_stock' => 100,
                'safety_stock' => 5,
            ]
        );

        $picanha->barcodes()->delete();
        $picanha->barcodes()->createMany([
            ['barcode' => '2001000000000', 'type' => ProductBarcode::TYPE_INTERNAL, 'is_main' => true],
            ['barcode' => '7891234567890', 'type' => ProductBarcode::TYPE_EAN13, 'is_main' => false],
        ]);

        $picanha->packages()->delete();
        $picanha->packages()->createMany([
            ['name' => 'Quilograma (KG)', 'unit_type' => 'KG', 'conversion_factor' => 1.0, 'barcode' => '2001000000000', 'is_default_sale' => true, 'status' => true],
            ['name' => 'Caixa Fechada c/ 10 Peças a Vácuo (~15kg)', 'unit_type' => 'CX', 'conversion_factor' => 15.0, 'barcode' => null, 'is_default_sale' => false, 'status' => true],
        ]);

        // 3.4 Arroz Branco Camil Tipo 1 Pacote 5kg
        $arroz = Product::firstOrCreate(
            ['sku' => 'MER-ARROZ-CAMIL-5KG', 'company_id' => $companyId],
            [
                'internal_code' => '3001',
                'name' => 'Arroz Branco Camil Tipo 1 Pacote 5kg',
                'short_description' => 'ARROZ CAMIL BRANCO 5KG',
                'description' => 'Arroz longo fino tipo 1 selecionado.',
                'brand_id' => $brands['camil']->id,
                'department_id' => $depMercearia->id,
                'category_id' => $catGraos->id,
                'subcategory_id' => $subcatArroz->id,
                'unit_of_measure' => 'UN',
                'is_weighable' => false,
                'status' => 'active',
                'ncm' => '1006.30.21',
                'cest' => '17.051.00',
                'origin' => '0',
                'cst_csosn' => '0102',
                'cfop_default' => '5102',
                'net_weight' => 5.000,
                'gross_weight' => 5.040,
                'volume' => '5kg',
                'minimum_stock' => 100,
                'maximum_stock' => 1000,
                'safety_stock' => 50,
            ]
        );

        $arroz->barcodes()->delete();
        $arroz->barcodes()->createMany([
            ['barcode' => '7896006700018', 'type' => ProductBarcode::TYPE_EAN13, 'is_main' => true],
            ['barcode' => '17896006700015', 'type' => ProductBarcode::TYPE_GTIN, 'is_main' => false],
        ]);

        $arroz->packages()->delete();
        $arroz->packages()->createMany([
            ['name' => 'Pacote 5kg', 'unit_type' => 'UN', 'conversion_factor' => 1.0, 'barcode' => '7896006700018', 'is_default_sale' => true, 'status' => true],
            ['name' => 'Fardo c/ 6 Pacotes (30kg)', 'unit_type' => 'FD', 'conversion_factor' => 6.0, 'barcode' => '17896006700015', 'is_default_sale' => false, 'status' => true],
        ]);

        // 3.5 Sabão em Pó Omo Lavagem Perfeita 1.6kg
        $omo = Product::firstOrCreate(
            ['sku' => 'LIMP-OMO-PO-1600', 'company_id' => $companyId],
            [
                'internal_code' => '4001',
                'name' => 'Lava Roupas em Pó Omo Lavagem Perfeita Caixa 1.6kg',
                'short_description' => 'SABAO PO OMO 1.6KG',
                'description' => 'Detergente em pó de ação profunda contra manchas difíceis.',
                'brand_id' => $brands['omo']->id,
                'department_id' => $depLimpeza->id,
                'category_id' => $catLavaRoupas->id,
                'subcategory_id' => $subcatPo->id,
                'unit_of_measure' => 'UN',
                'is_weighable' => false,
                'status' => 'active',
                'ncm' => '3402.20.00',
                'cest' => '11.001.00',
                'origin' => '0',
                'cst_csosn' => '0102',
                'cfop_default' => '5405',
                'net_weight' => 1.600,
                'gross_weight' => 1.680,
                'volume' => '1.6kg',
                'minimum_stock' => 40,
                'maximum_stock' => 400,
                'safety_stock' => 20,
            ]
        );

        $omo->barcodes()->delete();
        $omo->barcodes()->createMany([
            ['barcode' => '7891150041234', 'type' => ProductBarcode::TYPE_EAN13, 'is_main' => true],
            ['barcode' => '78911500', 'type' => ProductBarcode::TYPE_EAN8, 'is_main' => false],
        ]);

        $omo->packages()->delete();
        $omo->packages()->createMany([
            ['name' => 'Caixa 1.6kg', 'unit_type' => 'UN', 'conversion_factor' => 1.0, 'barcode' => '7891150041234', 'is_default_sale' => true, 'status' => true],
            ['name' => 'Fardo Caixa Fechada c/ 12 unidades', 'unit_type' => 'FD', 'conversion_factor' => 12.0, 'barcode' => '17891150041231', 'is_default_sale' => false, 'status' => true],
        ]);
    }
}
