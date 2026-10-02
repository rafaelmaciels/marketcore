@extends('layouts.app')

@section('title', 'Nova Filial')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <x-breadcrumbs :items="[['label' => 'Filiais', 'url' => route('branches.index')], ['label' => 'Nova Filial']]" />
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Cadastrar Nova Filial</h1>
            <p class="text-xs text-slate-500 mt-0.5">Adiciona uma nova loja física ou centro de distribuição à rede</p>
        </div>

        <x-button :href="route('branches.index')" variant="secondary" icon="arrow-left">
            Voltar
        </x-button>
    </div>

    <!-- Formulário em Card SaaS -->
    <form action="{{ route('branches.store') }}" method="POST">
        @csrf

        <div class="space-y-6">
            <!-- Bloco 1: Dados Institucionais -->
            <x-card title="Informações Gerais & Fiscais" subtitle="Identificação da filial e vínculo com a empresa">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div class="sm:col-span-2 lg:col-span-3">
                        <x-select label="Empresa / Razão Social" name="company_id" required>
                            <option value="">Selecione a empresa proprietária...</option>
                            @foreach($companies as $c)
                                <option value="{{ $c->id }}" {{ old('company_id') == $c->id ? 'selected' : '' }}>
                                    {{ $c->name }} (CNPJ: {{ $c->cnpj }})
                                </option>
                            @endforeach
                        </x-select>
                    </div>

                    <div class="sm:col-span-2">
                        <x-input label="Nome da Filial / Loja" name="name" placeholder="Ex: Loja 03 - Shopping Norte" required />
                    </div>

                    <div>
                        <x-input label="Código Interno" name="code" placeholder="003" />
                    </div>

                    <div>
                        <x-input label="CNPJ da Filial" name="cnpj" placeholder="00.000.000/0000-00" required />
                    </div>

                    <div>
                        <x-input label="Inscrição Estadual" name="ie" placeholder="112.233.445.566" />
                    </div>

                    <div>
                        <x-input label="Telefone de Contato" name="phone" placeholder="(11) 3344-5500" />
                    </div>

                    <div class="sm:col-span-2">
                        <x-input label="E-mail Operacional" name="email" type="email" placeholder="loja03@supermercado.com.br" />
                    </div>

                    <div class="sm:col-span-2 lg:col-span-3 flex flex-wrap items-center gap-6 pt-2">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="is_headquarters" value="1" {{ old('is_headquarters') ? 'checked' : '' }}
                                   class="w-4 h-4 rounded-md border-slate-300 text-blue-600 focus:ring-blue-500/20">
                            <span class="text-xs font-medium text-slate-700">Definir como Matriz da Rede</span>
                        </label>

                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="status" value="1" {{ old('status', true) ? 'checked' : '' }}
                                   class="w-4 h-4 rounded-md border-slate-300 text-blue-600 focus:ring-blue-500/20">
                            <span class="text-xs font-medium text-slate-700">Unidade Operacional Ativa</span>
                        </label>
                    </div>
                </div>
            </x-card>

            <!-- Bloco 2: Localização -->
            <x-card title="Endereço & Localização Física" subtitle="Dados para entrega, fiscal e mapeamento">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div>
                        <x-input label="CEP" name="zip_code" placeholder="01001-000" />
                    </div>

                    <div class="sm:col-span-2">
                        <x-input label="Logradouro / Rua / Avenida" name="address" placeholder="Av. Principal" />
                    </div>

                    <div>
                        <x-input label="Número" name="number" placeholder="1000" />
                    </div>

                    <div>
                        <x-input label="Complemento" name="complement" placeholder="Galpão B" />
                    </div>

                    <div>
                        <x-input label="Bairro" name="neighborhood" placeholder="Centro" />
                    </div>

                    <div>
                        <x-input label="Cidade" name="city" placeholder="São Paulo" />
                    </div>

                    <div>
                        <x-input label="UF" name="state" placeholder="SP" maxlength="2" />
                    </div>
                </div>
            </x-card>

            <!-- Card Dica Operacional -->
            <div class="p-4 rounded-2xl bg-blue-50/70 border border-blue-100 flex items-start gap-3 text-xs text-blue-800">
                <x-icon name="info" class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" />
                <div class="leading-relaxed">
                    <strong>Geração Automática de Depósitos:</strong> Ao cadastrar a filial, o sistema inicializa automaticamente a <strong>Área de Vendas</strong> e o <strong>Depósito Geral de Recebimento</strong>, garantindo integridade para o controle de estoques.
                </div>
            </div>

            <!-- Botões de Ação -->
            <div class="flex items-center justify-end gap-3 pt-2">
                <x-button :href="route('branches.index')" variant="secondary">
                    Cancelar
                </x-button>
                <x-button type="submit" variant="primary" icon="check">
                    Salvar Filial
                </x-button>
            </div>
        </div>
    </form>

</div>
@endsection
