<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Registrar Transação para {{ $asset->ticker }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form action="{{ route('assets.transactions.store', $asset) }}" method="POST">
                        @csrf
                        
                        <div class="mb-4">
                            <x-input-label for="type" :value="__('Tipo de Transação')" />
                            <select name="type" id="type" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                <option value="buy">Compra</option>
                                <option value="sell">Venda</option>
                            </select>
                            <x-input-error :messages="$errors->get('type')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="transaction_date" :value="__('Data da Transação')" />
                            <x-text-input id="transaction_date" class="block mt-1 w-full" type="date" name="transaction_date" :value="old('transaction_date', now()->format('Y-m-d'))" required />
                            <x-input-error :messages="$errors->get('transaction_date')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="quantity" :value="__('Quantidade')" />
                            <x-text-input id="quantity" class="block mt-1 w-full" type="number" step="any" name="quantity" :value="old('quantity')" required />
                            <x-input-error :messages="$errors->get('quantity')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="unit_price" :value="__('Preço por Unidade')" />
                            <x-text-input id="unit_price" class="block mt-1 w-full" type="number" step="any" name="unit_price" :value="old('unit_price')" required />
                            <x-input-error :messages="$errors->get('unit_price')" class="mt-2" />
                        </div>
                        
                        <div class="flex items-center justify-end mt-4">
                            <a href="{{ route('assets.transactions.index', $asset) }}" class="text-gray-600 hover:text-gray-900 mr-4">Cancelar</a>
                            <x-primary-button>
                                {{ __('Salvar Transação') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>