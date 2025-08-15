<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ isset($dividend) ? 'Editar' : 'Registrar' }} Provento para {{ $asset->ticker ?? $dividend->asset->ticker }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form action="{{ isset($dividend) ? route('dividends.update', $dividend) : route('assets.dividends.store', $asset) }}" method="POST">
                        @csrf
                        @if(isset($dividend))
                            @method('PUT')
                        @endif
                        
                        <div class="mb-4">
                            <x-input-label for="record_date" :value="__('Data Com')" />
                            <x-text-input id="record_date" class="block mt-1 w-full" type="date" name="record_date" :value="old('record_date', ($dividend->record_date ?? now())->format('Y-m-d'))" required />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="ex_date" :value="__('Data Ex')" />
                            <x-text-input id="ex_date" class="block mt-1 w-full" type="date" name="ex_date" :value="old('ex_date', ($dividend->ex_date ?? now())->format('Y-m-d'))" required />
                        </div>                        

                        <div class="mb-4">
                            <x-input-label for="payment_date" :value="__('Data do Pagamento')" />
                            <x-text-input id="payment_date" class="block mt-1 w-full" type="date" name="payment_date" :value="old('payment_date', ($dividend->payment_date ?? now())->format('Y-m-d'))" required />
                            <x-input-error :messages="$errors->get('payment_date')" class="mt-2" />
                        </div>
                        
                        <div class="mb-4">
                            <x-input-label for="type" :value="__('Tipo de Provento')" />
                            <select name="type" id="type" class="block mt-1 w-full border-gray-300 ...">
                                <option value="DIV" @selected(old('type', ($dividend->type ?? '')) == 'DIV')>Dividendo (DIV)</option>
                                <option value="JSCP" @selected(old('type', ($dividend->type ?? '')) == 'JSCP')>Juros S/ Capital Próprio (JSCP)</option>
                                <option value="REN" @selected(old('type', ($dividend->type ?? '')) == 'REN')>Rendimentos</option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <x-input-label for="amount_per_share" :value="__('Valor por Cota/Ação')" />
                            <x-text-input id="amount_per_share" class="block mt-1 w-full" type="number" step="any" name="amount_per_share" :value="old('amount_per_share', $dividend->amount_per_share ?? '')" required />
                            <x-input-error :messages="$errors->get('amount_per_share')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <a href="{{ route('assets.dividends.index', $asset ?? $dividend->asset_id) }}" class="text-gray-600 hover:text-gray-900 mr-4">Cancelar</a>
                            <x-primary-button>
                                Salvar
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>