@extends('layouts.app')

@section('title', 'Editar Filial')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <x-breadcrumbs :items="[['label' => 'Filiais', 'url' => route('branches.index')], ['label' => $branch->name, 'url' => route('branches.show', $branch)], ['label' => 'Editar']]" />
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Editar Filial: {{ $branch->name }}</h1>
            <p class="text-xs text-slate-500 mt-0.5">Atualização de dados cadastrais, fiscais e operacionais</p>
        </div>

        <x-button :href="route('branches.show', $branch)" variant="secondary" icon="arrow-left">
            Voltar
        </x-button>
    </div>

    <!-- Formulário em Card SaaS -->
    <form action="{{ route('branches.update', $branch) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="space-y-6">
            <!-- Bloco 1: Dados Institucionais -->
            <x-card title="Informações Gerais & Fiscais" subtitle="Identificação da filial e vínculo com a empresa">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div class="sm:col-span-2 lg:col-span-3">
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5 uppercase tracking-wider">Empresa / Razão Social</label>
                        <div class="h-10 px-3.5 flex items-center bg-slate-50 border border-slate-200/80 rounded-xl text-xs text-slate-500 font-medium">
                            {{ $branch->company?->name }} (CNPJ: {{ $branch->company?->cnpj }})
                        </div>
                    </div>

                    <div class="sm:col-span-2">
                        <x-input label="Nome da Filial / Loja" name="name" :value="$branch->name" required />
                    </div>

                    <div>
                        <x-input label="Código Interno" name="code" :value="$branch->code" />
                    </div>

                    <div>
                        <x-input label="CNPJ da Filial" name="cnpj" :value="$branch->cnpj" required />
                    </div>

                    <div>
                        <x-input label="Inscrição Estadual" name="ie" :value="$branch->ie" />
                    </div>

                    <div>
                        <x-input label="Telefone de Contato" name="phone" :value="$branch->phone" />
                    </div>

                    <div class="sm:col-span-2">
                        <x-input label="E-mail Operacional" name="email" type="email" :value="$branch->email" />
                    </div>

                    <div class="sm:col-span-2 lg:col-span-3 flex flex-wrap items-center gap-6 pt-2">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="is_headquarters" value="1" {{ old('is_headquarters', $branch->is_headquarters) ? 'checked' : '' }}
                                   class="w-4 h-4 rounded-md border-slate-300 text-blue-600 focus:ring-blue-500/20">
                            <span class="text-xs font-medium text-slate-700">Definir como Matriz da Rede</span>
                        </label>

                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="status" value="1" {{ old('status', $branch->status) ? 'checked' : '' }}
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
                        <x-input label="CEP" name="zip_code" :value="$branch->zip_code" />
                    </div>

                    <div class="sm:col-span-2">
                        <x-input label="Logradouro / Rua / Avenida" name="address" :value="$branch->address" />
                    </div>

                    <div>
                        <x-input label="Número" name="number" :value="$branch->number" />
                    </div>

                    <div>
                        <x-input label="Complemento" name="complement" :value="$branch->complement" />
                    </div>

                    <div>
                        <x-input label="Bairro" name="neighborhood" :value="$branch->neighborhood" />
                    </div>

                    <div>
                        <x-input label="Cidade" name="city" :value="$branch->city" />
                    </div>

                    <div>
                        <x-input label="UF" name="state" :value="$branch->state" maxlength="2" />
                    </div>
                </div>
            </x-card>

            <!-- Botões de Ação -->
            <div class="flex items-center justify-end gap-3 pt-2">
                <x-button :href="route('branches.show', $branch)" variant="secondary">
                    Cancelar
                </x-button>
                <x-button type="submit" variant="primary" icon="check">
                    Salvar Alterações
                </x-button>
            </div>
        </div>
    </form>

</div>
@endsection
