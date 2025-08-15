<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Criar Novo Ativo') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form action="{{ route('assets.store') }}" method="POST">
                        @csrf

                        <div class="mb-4">
                            <x-input-label for="portfolio_id" :value="__('Carteira')" />
                            <select name="portfolio_id" id="portfolio_id" class="block mt-1 w-full border-gray-300 ...">
                                <option value="">Sem Carteira</option>
                                @foreach ($portfolios as $portfolio)
                                    <option value="{{ $portfolio->id }}" @selected(old('portfolio_id', $asset->portfolio_id ?? '') == $portfolio->id)>
                                        {{ $portfolio->name }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('portfolio_id')" class="mt-2" />
                        </div>                        
                        
                        <div class="mb-4">
                            <x-input-label for="ticker" :value="__('Ticker')" />
                            <x-text-input id="ticker" class="block mt-1 w-full uppercase" type="text" name="ticker" :value="old('ticker')" required autofocus />
                            <x-input-error :messages="$errors->get('ticker')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="name" :value="__('Nome do Ativo')" />
                            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="type" :value="__('Tipo de Ativo')" />
                            <select name="type" id="type" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                <option value="stock">Ação (Stock)</option>
                                <option value="fii">Fundo Imobiliário (FII)</option>
                                <option value="crypto">Criptomoeda</option>
                                <option value="fixed_income">Renda Fixa</option>
                                <option value="currency" @selected(old('type', $asset->type ?? '') == 'currency')>Moeda</option>
                                <option value="benchmark" @selected(old('type', $asset->type ?? '') == 'benchmark')>Índice</option>
                            </select>
                            <x-input-error :messages="$errors->get('type')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="currency" :value="__('Moeda')" />
                            <select name="currency" id="currency" class="block mt-1 w-full border-gray-300 ...">
                                <option value="BRL" @selected(old('currency', $asset->currency ?? '') == 'BRL')>Real (BRL)</option>
                                <option value="USD" @selected(old('currency', $asset->currency ?? '') == 'USD')>Dólar (USD)</option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <x-input-label for="target_percentage" :value="__('Percentual Objetivo na Carteira (%)')" />
                            <x-text-input id="target_percentage" class="block mt-1 w-full" type="number" step="0.01" name="target_percentage" :value="old('target_percentage', $asset->target_percentage ?? '0.00')" />
                            <x-input-error :messages="$errors->get('target_percentage')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="logo_url" :value="__('Nome do Arquivo do Logo (ex: petr4.png)')" />
                            <x-text-input id="logo_url" class="block mt-1 w-full" type="text" name="logo_url" :value="old('logo_url', $asset->logo_url ?? '')" />
                            <x-input-error :messages="$errors->get('logo_url')" class="mt-2" />
                        </div>                        
                        
                        <div class="flex items-center justify-end mt-4">
                            <a href="{{ route('assets.index') }}" class="text-gray-600 hover:text-gray-900 mr-4">Cancelar</a>
                            <x-primary-button>
                                {{ __('Salvar Ativo') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>