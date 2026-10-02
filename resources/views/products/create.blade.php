@extends('layouts.app')

@section('title', 'Novo Produto')

@section('content')
<div class="space-y-6" x-data="{
    unitOfMeasure: 'UN',
    isWeighable: false,
    selectedDept: '{{ old('department_id', '') }}',
    selectedCat: '{{ old('category_id', '') }}',
    selectedSubcat: '{{ old('subcategory_id', '') }}',
    allCategories: {{ Js::from($categories) }},
    allSubcategories: {{ Js::from($subcategories) }},
    
    get filteredCategories() {
        if (!this.selectedDept) return [];
        return this.allCategories.filter(c => c.parent_id == this.selectedDept);
    },

    get filteredSubcategories() {
        if (!this.selectedCat) return [];
        return this.allSubcategories.filter(s => s.parent_id == this.selectedCat);
    },

    barcodes: [
        { barcode: '{{ old('barcodes.0.barcode', '') }}', type: 'ean13', is_main: true }
    ],
    addBarcode() {
        this.barcodes.push({ barcode: '', type: 'ean13', is_main: false });
    },
    removeBarcode(index) {
        if (this.barcodes.length > 1) {
            this.barcodes.splice(index, 1);
        }
    },
    setMainBarcode(index) {
        this.barcodes.forEach((b, i) => b.is_main = (i === index));
    },

    packages: [
        { name: 'Unidade', unit_type: 'UN', conversion_factor: 1.0, barcode: '', is_default_sale: true }
    ],
    addPackage() {
        this.packages.push({ name: '', unit_type: 'CX', conversion_factor: 12.0, barcode: '', is_default_sale: false });
    },
    removePackage(index) {
        if (this.packages.length > 1) {
            this.packages.splice(index, 1);
        }
    },
    setDefaultPackage(index) {
        this.packages.forEach((p, i) => p.is_default_sale = (i === index));
    }
}">

    <!-- Header da Página -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <x-breadcrumbs :items="[['label' => 'Produtos', 'url' => route('products.index')], ['label' => 'Novo Produto']]" />
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Cadastrar Novo Produto</h1>
            <p class="text-xs text-slate-500 mt-0.5">Preencha os dados cadastrais, códigos de barras, regras de embalagem e parâmetros fiscais</p>
        </div>

        <div class="flex items-center gap-2.5">
            <x-button :href="route('products.index')" variant="secondary" icon="arrow-left">
                Voltar
            </x-button>
            <x-button type="submit" form="productForm" variant="primary" icon="check">
                Salvar Produto
            </x-button>
        </div>
    </div>

    @if ($errors->any())
        <x-alert variant="danger" title="Atenção aos erros de preenchimento:">
            <ul class="list-disc list-inside text-xs space-y-1 mt-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </x-alert>
    @endif

    <form id="productForm" action="{{ route('products.store') }}" method="POST" class="space-y-6">
        @csrf

        <!-- 1. IDENTIFICAÇÃO GERAL -->
        <x-card title="1. Identificação Geral do Item" subtitle="Dados essenciais de cadastro, nomenclatura e classificação">
            <div class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <x-input
                            name="sku"
                            label="SKU (Identificador Único)"
                            placeholder="Ex: BEB-COCA-2L"
                            value="{{ old('sku') }}"
                            required
                        />
                        <span class="text-[11px] text-slate-400 mt-1 block">Código único de catálogo</span>
                    </div>

                    <div>
                        <x-input
                            name="internal_code"
                            label="Código Interno (ERP)"
                            placeholder="Ex: 001045"
                            value="{{ old('internal_code') }}"
                        />
                        <span class="text-[11px] text-slate-400 mt-1 block">Código legado ou de balança</span>
                    </div>

                    <div>
                        <x-select name="status" label="Status Cadastral" required>
                            <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Ativo</option>
                            <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inativo</option>
                            <option value="discontinued" {{ old('status') === 'discontinued' ? 'selected' : '' }}>Descontinuado</option>
                        </x-select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="sm:col-span-2">
                        <x-input
                            name="name"
                            label="Nome Completo do Produto"
                            placeholder="Ex: Refrigerante Coca-Cola Garrafa PET 2 Litros"
                            value="{{ old('name') }}"
                            required
                        />
                    </div>
                    <div>
                        <x-input
                            name="short_description"
                            label="Descrição Curta (PDV / Cupom)"
                            placeholder="Ex: REFRIG COCA-COLA 2L"
                            value="{{ old('short_description') }}"
                        />
                    </div>
                </div>

                <!-- Mercadológico & Marca -->
                <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 pt-2">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5 uppercase tracking-wider">Marca</label>
                        <x-select name="brand_id">
                            <option value="">Selecione a Marca...</option>
                            @foreach($brands as $brand)
                                <option value="{{ $brand->id }}" {{ old('brand_id') == $brand->id ? 'selected' : '' }}>
                                    {{ $brand->name }}
                                </option>
                            @endforeach
                        </x-select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5 uppercase tracking-wider">1. Departamento</label>
                        <select
                            name="department_id"
                            x-model="selectedDept"
                            @change="selectedCat = ''; selectedSubcat = ''"
                            class="w-full h-10 text-sm bg-white rounded-xl border border-slate-200/90 px-3.5 text-slate-800 focus:outline-none focus:ring-2 focus:border-blue-500"
                        >
                            <option value="">Selecione o Departamento...</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5 uppercase tracking-wider">2. Categoria</label>
                        <select
                            name="category_id"
                            x-model="selectedCat"
                            @change="selectedSubcat = ''"
                            :disabled="!selectedDept || filteredCategories.length === 0"
                            class="w-full h-10 text-sm bg-white rounded-xl border border-slate-200/90 px-3.5 text-slate-800 focus:outline-none focus:ring-2 focus:border-blue-500 disabled:bg-slate-50 disabled:text-slate-400"
                        >
                            <option value="">Selecione a Categoria...</option>
                            <template x-for="cat in filteredCategories" :key="cat.id">
                                <option :value="cat.id" x-text="cat.name" :selected="cat.id == selectedCat"></option>
                            </template>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5 uppercase tracking-wider">3. Subcategoria</label>
                        <select
                            name="subcategory_id"
                            x-model="selectedSubcat"
                            :disabled="!selectedCat || filteredSubcategories.length === 0"
                            class="w-full h-10 text-sm bg-white rounded-xl border border-slate-200/90 px-3.5 text-slate-800 focus:outline-none focus:ring-2 focus:border-blue-500 disabled:bg-slate-50 disabled:text-slate-400"
                        >
                            <option value="">Selecione a Subcategoria...</option>
                            <template x-for="sub in filteredSubcategories" :key="sub.id">
                                <option :value="sub.id" x-text="sub.name" :selected="sub.id == selectedSubcat"></option>
                            </template>
                        </select>
                    </div>
                </div>

                <!-- Unidade e Pesável -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-2">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5 uppercase tracking-wider">Unidade de Venda Base <span class="text-rose-500">*</span></label>
                        <select
                            name="unit_of_measure"
                            x-model="unitOfMeasure"
                            required
                            class="w-full h-10 text-sm bg-white rounded-xl border border-slate-200/90 px-3.5 text-slate-800 focus:outline-none focus:ring-2 focus:border-blue-500"
                        >
                            <option value="UN">UN - Unidade</option>
                            <option value="KG">KG - Quilograma</option>
                            <option value="G">G - Grama</option>
                            <option value="LT">LT - Litro</option>
                            <option value="ML">ML - Mililitro</option>
                            <option value="CX">CX - Caixa</option>
                            <option value="FD">FD - Fardo</option>
                            <option value="PCT">PCT - Pacote</option>
                            <option value="PAR">PAR - Par</option>
                            <option value="M">M - Metro</option>
                        </select>
                    </div>

                    <div class="sm:col-span-2 flex items-center pt-6">
                        <label class="inline-flex items-center gap-2 cursor-pointer select-none">
                            <input
                                type="checkbox"
                                name="is_weighable"
                                value="1"
                                x-model="isWeighable"
                                class="w-4 h-4 rounded text-blue-600 border-slate-300 focus:ring-blue-500"
                            />
                            <span class="text-xs font-semibold text-slate-800 flex items-center gap-1.5">
                                <x-icon name="scale" class="w-4 h-4 text-emerald-600" />
                                <span>Produto Pesável / Balança (Gera etiqueta com peso ou código interno)</span>
                            </span>
                        </label>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5 uppercase tracking-wider">Descrição Detalhada / Ficha Técnica</label>
                    <textarea
                        name="description"
                        rows="3"
                        class="w-full text-sm bg-white rounded-xl border border-slate-200/90 p-3 text-slate-800 focus:outline-none focus:ring-2 focus:border-blue-500"
                        placeholder="Informações completas de especificações, ingredientes, modo de conservação..."
                    >{{ old('description') }}</textarea>
                </div>
            </div>
        </x-card>

        <!-- 2. CÓDIGOS DE BARRAS MÚLTIPLOS -->
        <x-card title="2. Códigos de Barras (Múltiplos)" subtitle="Suporte a EAN-13, EAN-8, GTIN e código interno para identificação no PDV">
            <x-slot:action>
                <button
                    type="button"
                    @click="addBarcode()"
                    class="inline-flex items-center gap-1 text-xs font-semibold text-blue-600 hover:text-blue-700 bg-blue-50 hover:bg-blue-100 px-3 py-1.5 rounded-lg transition-colors"
                >
                    <x-icon name="plus" class="w-3.5 h-3.5" />
                    <span>Adicionar Código</span>
                </button>
            </x-slot:action>

            <div class="space-y-3">
                <template x-for="(item, index) in barcodes" :key="index">
                    <div class="flex flex-col sm:flex-row items-center gap-3 p-3 rounded-xl bg-slate-50/70 border border-slate-200/80">
                        <div class="w-full sm:w-44">
                            <label class="block text-[10px] font-semibold text-slate-500 mb-1 uppercase">Padrão / Tipo</label>
                            <select
                                :name="'barcodes[' + index + '][type]'"
                                x-model="item.type"
                                class="w-full h-9 text-xs bg-white rounded-lg border border-slate-200 px-2.5 text-slate-800 focus:outline-none focus:ring-2 focus:border-blue-500"
                            >
                                <option value="ean13">EAN-13 (Comum)</option>
                                <option value="ean8">EAN-8 (Reduzido)</option>
                                <option value="gtin">GTIN-14 / DUN</option>
                                <option value="internal">Código Interno</option>
                            </select>
                        </div>

                        <div class="flex-1 w-full">
                            <label class="block text-[10px] font-semibold text-slate-500 mb-1 uppercase">Código Numérico</label>
                            <input
                                type="text"
                                :name="'barcodes[' + index + '][barcode]'"
                                x-model="item.barcode"
                                placeholder="Digite o código (ex: 7894900010015)..."
                                class="w-full h-9 text-xs font-mono bg-white rounded-lg border border-slate-200 px-3 text-slate-800 focus:outline-none focus:ring-2 focus:border-blue-500"
                            />
                        </div>

                        <div class="w-full sm:w-32 flex items-center sm:justify-center pt-2 sm:pt-4">
                            <label class="inline-flex items-center gap-1.5 cursor-pointer text-xs">
                                <input
                                    type="radio"
                                    name="main_barcode_radio"
                                    :checked="item.is_main"
                                    @change="setMainBarcode(index)"
                                    class="w-3.5 h-3.5 text-blue-600 focus:ring-blue-500"
                                />
                                <input
                                    type="hidden"
                                    :name="'barcodes[' + index + '][is_main]'"
                                    :value="item.is_main ? '1' : '0'"
                                />
                                <span class="text-[11px] font-medium text-slate-700">Principal</span>
                            </label>
                        </div>

                        <div class="pt-2 sm:pt-4">
                            <button
                                type="button"
                                @click="removeBarcode(index)"
                                :disabled="barcodes.length <= 1"
                                class="p-2 text-slate-400 hover:text-rose-600 disabled:opacity-30 disabled:hover:text-slate-400 transition-colors"
                                title="Remover Código"
                            >
                                <x-icon name="trash-2" class="w-4 h-4" />
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </x-card>

        <!-- 3. SISTEMA DE EMBALAGENS E CONVERSÃO -->
        <x-card title="3. Embalagens & Conversão de Unidades" subtitle="Defina múltiplos formatos de comercialização (ex: Garrafa individual, Pacote c/ 6, Caixa c/ 24)">
            <x-slot:action>
                <button
                    type="button"
                    @click="addPackage()"
                    class="inline-flex items-center gap-1 text-xs font-semibold text-blue-600 hover:text-blue-700 bg-blue-50 hover:bg-blue-100 px-3 py-1.5 rounded-lg transition-colors"
                >
                    <x-icon name="plus" class="w-3.5 h-3.5" />
                    <span>Nova Embalagem</span>
                </button>
            </x-slot:action>

            <div class="space-y-3">
                <template x-for="(pkg, index) in packages" :key="index">
                    <div class="flex flex-col sm:flex-row items-center gap-3 p-3 rounded-xl bg-slate-50/70 border border-slate-200/80">
                        <div class="w-full sm:w-48">
                            <label class="block text-[10px] font-semibold text-slate-500 mb-1 uppercase">Descrição da Embalagem</label>
                            <input
                                type="text"
                                :name="'packages[' + index + '][name]'"
                                x-model="pkg.name"
                                placeholder="Ex: Pacote c/ 6, Caixa c/ 24..."
                                required
                                class="w-full h-9 text-xs bg-white rounded-lg border border-slate-200 px-2.5 text-slate-800 focus:outline-none focus:ring-2 focus:border-blue-500"
                            />
                        </div>

                        <div class="w-full sm:w-28">
                            <label class="block text-[10px] font-semibold text-slate-500 mb-1 uppercase">Sigla Emb.</label>
                            <select
                                :name="'packages[' + index + '][unit_type]'"
                                x-model="pkg.unit_type"
                                class="w-full h-9 text-xs bg-white rounded-lg border border-slate-200 px-2 text-slate-800 focus:outline-none focus:ring-2 focus:border-blue-500"
                            >
                                <option value="UN">UN</option>
                                <option value="PCT">PCT</option>
                                <option value="CX">CX</option>
                                <option value="FD">FD</option>
                                <option value="DISPLAY">DISP</option>
                                <option value="FARDO">FARDO</option>
                            </select>
                        </div>

                        <div class="w-full sm:w-36">
                            <label class="block text-[10px] font-semibold text-slate-500 mb-1 uppercase">Fator Conversão</label>
                            <input
                                type="number"
                                step="0.001"
                                min="0.001"
                                :name="'packages[' + index + '][conversion_factor]'"
                                x-model="pkg.conversion_factor"
                                required
                                class="w-full h-9 text-xs font-mono bg-white rounded-lg border border-slate-200 px-2.5 text-slate-800 focus:outline-none focus:ring-2 focus:border-blue-500"
                            />
                        </div>

                        <div class="flex-1 w-full">
                            <label class="block text-[10px] font-semibold text-slate-500 mb-1 uppercase">Código de Barras da Embalagem</label>
                            <input
                                type="text"
                                :name="'packages[' + index + '][barcode]'"
                                x-model="pkg.barcode"
                                placeholder="DUN-14 ou código impresso na caixa (opcional)..."
                                class="w-full h-9 text-xs font-mono bg-white rounded-lg border border-slate-200 px-2.5 text-slate-800 focus:outline-none focus:ring-2 focus:border-blue-500"
                            />
                        </div>

                        <div class="w-full sm:w-28 flex items-center sm:justify-center pt-2 sm:pt-4">
                            <label class="inline-flex items-center gap-1.5 cursor-pointer text-xs">
                                <input
                                    type="radio"
                                    name="default_package_radio"
                                    :checked="pkg.is_default_sale"
                                    @change="setDefaultPackage(index)"
                                    class="w-3.5 h-3.5 text-blue-600 focus:ring-blue-500"
                                />
                                <input
                                    type="hidden"
                                    :name="'packages[' + index + '][is_default_sale]'"
                                    :value="pkg.is_default_sale ? '1' : '0'"
                                />
                                <span class="text-[11px] font-medium text-slate-700">Padrão Venda</span>
                            </label>
                        </div>

                        <div class="pt-2 sm:pt-4">
                            <button
                                type="button"
                                @click="removePackage(index)"
                                :disabled="packages.length <= 1"
                                class="p-2 text-slate-400 hover:text-rose-600 disabled:opacity-30 disabled:hover:text-slate-400 transition-colors"
                                title="Remover Embalagem"
                            >
                                <x-icon name="trash-2" class="w-4 h-4" />
                            </button>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Resumo Visual Interativo de Conversão -->
            <div class="mt-4 p-3.5 rounded-xl bg-blue-50/60 border border-blue-100 flex items-start gap-2.5 text-xs text-blue-900">
                <x-icon name="info" class="w-4 h-4 text-blue-600 flex-shrink-0 mt-0.5" />
                <div class="space-y-1">
                    <span class="font-semibold block">Regra de Conversão Ativa:</span>
                    <template x-for="(p, i) in packages" :key="i">
                        <div class="text-[11px] font-mono text-blue-800">
                            &bull; 1 <span x-text="p.name || 'Embalagem'"></span> = <strong x-text="p.conversion_factor"></strong> <span x-text="unitOfMeasure"></span> (Unidades Base)
                        </div>
                    </template>
                </div>
            </div>
        </x-card>

        <!-- 4. DADOS FISCAIS -->
        <x-card title="4. Tributação & Parâmetros Fiscais" subtitle="Identificadores para emissão de NFC-e, NF-e e conformidade tributária">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                <div>
                    <x-input
                        name="ncm"
                        label="NCM"
                        placeholder="Ex: 2202.10.00"
                        value="{{ old('ncm') }}"
                    />
                    <span class="text-[10px] text-slate-400 mt-1 block">8 dígitos obrigatório NF-e</span>
                </div>

                <div>
                    <x-input
                        name="cest"
                        label="CEST"
                        placeholder="Ex: 03.011.00"
                        value="{{ old('cest') }}"
                    />
                    <span class="text-[10px] text-slate-400 mt-1 block">Substituição Tributária</span>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5 uppercase tracking-wider">Origem da Mercadoria</label>
                    <select name="origin" class="w-full h-10 text-xs bg-white rounded-xl border border-slate-200/90 px-2.5 text-slate-800 focus:outline-none focus:ring-2 focus:border-blue-500">
                        <option value="0" {{ old('origin', '0') == '0' ? 'selected' : '' }}>0 - Nacional</option>
                        <option value="1" {{ old('origin') == '1' ? 'selected' : '' }}>1 - Estrangeira - Imp. Direta</option>
                        <option value="2" {{ old('origin') == '2' ? 'selected' : '' }}>2 - Estrangeira - Merc. Interno</option>
                        <option value="3" {{ old('origin') == '3' ? 'selected' : '' }}>3 - Nacional mercadoria > 40%</option>
                    </select>
                </div>

                <div>
                    <x-input
                        name="cst_csosn"
                        label="CST / CSOSN"
                        placeholder="Ex: 0102 ou 00"
                        value="{{ old('cst_csosn') }}"
                    />
                    <span class="text-[10px] text-slate-400 mt-1 block">Regime normal ou simples</span>
                </div>

                <div>
                    <x-input
                        name="cfop_default"
                        label="CFOP Padrão"
                        placeholder="Ex: 5102 ou 5405"
                        value="{{ old('cfop_default', '5102') }}"
                    />
                    <span class="text-[10px] text-slate-400 mt-1 block">Operação padrão de saída</span>
                </div>
            </div>
        </x-card>

        <!-- 5. DIMENSÕES & PARÂMETROS DE ESTOQUE TEÓRICOS -->
        <x-card title="5. Pesos, Medidas & Parâmetros de Estoque" subtitle="Especificações de logística e limites de segurança de estoque">
            <div class="grid grid-cols-1 sm:grid-cols-3 lg:grid-cols-6 gap-4">
                <div>
                    <x-input
                        name="net_weight"
                        label="Peso Líq. (KG)"
                        placeholder="0.000"
                        type="number"
                        step="0.001"
                        min="0"
                        value="{{ old('net_weight') }}"
                    />
                </div>

                <div>
                    <x-input
                        name="gross_weight"
                        label="Peso Bruto (KG)"
                        placeholder="0.000"
                        type="number"
                        step="0.001"
                        min="0"
                        value="{{ old('gross_weight') }}"
                    />
                </div>

                <div>
                    <x-input
                        name="volume"
                        label="Volume (m³)"
                        placeholder="0.0000"
                        value="{{ old('volume') }}"
                    />
                </div>

                <div>
                    <x-input
                        name="minimum_stock"
                        label="Estoque Mínimo"
                        placeholder="0"
                        type="number"
                        step="0.001"
                        min="0"
                        value="{{ old('minimum_stock', 0) }}"
                    />
                </div>

                <div>
                    <x-input
                        name="maximum_stock"
                        label="Estoque Máximo"
                        placeholder="0"
                        type="number"
                        step="0.001"
                        min="0"
                        value="{{ old('maximum_stock') }}"
                    />
                </div>

                <div>
                    <x-input
                        name="safety_stock"
                        label="Estoque Segurança"
                        placeholder="0"
                        type="number"
                        step="0.001"
                        min="0"
                        value="{{ old('safety_stock', 0) }}"
                    />
                </div>
            </div>
        </x-card>

        <!-- Footer com Botões de Ação -->
        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200/80">
            <x-button :href="route('products.index')" variant="ghost">
                Cancelar
            </x-button>
            <x-button type="submit" variant="primary" icon="check">
                Salvar Produto
            </x-button>
        </div>
    </form>

</div>
@endsection
