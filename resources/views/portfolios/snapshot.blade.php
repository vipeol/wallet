<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Posição da Carteira na Data') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- Formulário de Filtro de Data --}}
            <div class="mb-6 p-6 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <form action="{{ route('portfolios.snapshot') }}" method="GET">
                    <div class="flex items-end space-x-4">
                        <div>
                            <x-input-label for="snapshot_date" :value="__('Consultar Posição em:')" />
                            <x-text-input id="snapshot_date" class="block mt-1" type="date" name="snapshot_date" :value="$snapshotDate->format('Y-m-d')" required />
                        </div>
                        <x-primary-button>
                            {{ __('Consultar') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>

            {{-- Loop para cada Carteira --}}
            @forelse ($snapshotData as $portfolioName => $assets)
                <div class="mb-8">
                    <h3 class="text-2xl font-bold text-gray-800 dark:text-gray-200 mb-4">{{ $portfolioName }}</h3>
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <table class="min-w-full bg-white text-sm">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="py-2 px-3 border-b text-left">Ativo</th>
                                    <th class="py-2 px-3 border-b text-right">Posição</th>
                                    <th class="py-2 px-3 border-b text-right">Preço Médio (R$)</th>
                                    <th class="py-2 px-3 border-b text-right">Cotação na Data (R$)</th>
                                    <th class="py-2 px-3 border-b text-right">Valor de Mercado (R$)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($assets as $asset)
                                    <tr class="hover:bg-gray-50">
                                        <td class="py-2 px-3 border-b font-mono">
                                            <div class="flex items-center">
                                                @if ($asset['logo_path'])
                                                    <img src="{{ $asset['logo_path'] }}" alt="Logo de {{ $asset['ticker'] }}" class="w-6 h-6 mr-3 rounded-full">
                                                @else
                                                    <div class="w-6 h-6 mr-3 rounded-full bg-gray-200 flex items-center justify-center text-xs font-bold text-gray-500">{{ substr($asset['ticker'], 0, 2) }}</div>
                                                @endif
                                                <span>{{ $asset['ticker'] }}</span>
                                            </div>
                                        </td>
                                        <td class="py-2 px-3 border-b text-right font-semibold">{{ number_format($asset['quantity'], 2, ',', '.') }}</td>
                                        <td class="py-2 px-3 border-b text-right">{{ number_format($asset['average_price'], 2, ',', '.') }}</td>
                                        <td class="py-2 px-3 border-b text-right">{{ number_format($asset['latest_price'], 2, ',', '.') }}</td>
                                        <td class="py-2 px-3 border-b text-right font-bold">{{ number_format($asset['market_value'], 2, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-gray-50 font-bold">
                                <tr>
                                    <td colspan="4" class="py-2 px-3 border-b text-right">Subtotal em {{ $portfolioName }}:</td>
                                    <td class="py-2 px-3 border-b text-right">R$ {{ number_format($assets->sum('market_value'), 2, ',', '.') }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            @empty
                <div class="p-6 bg-white overflow-hidden shadow-sm sm:rounded-lg text-center text-gray-500">
                    Nenhuma posição encontrada para a data de {{ $snapshotDate->format('d/m/Y') }}.
                </div>
            @endforelse
            
            {{-- Total Geral --}}
            <div class="mt-8 p-6 bg-gray-800 text-white overflow-hidden shadow-sm sm:rounded-lg flex justify-between items-center">
                <h3 class="text-2xl font-bold">Patrimônio Total em {{ $snapshotDate->format('d/m/Y') }}</h3>
                <p class="text-3xl font-extrabold">R$ {{ number_format($grandTotal, 2, ',', '.') }}</p>
            </div>
        </div>
    </div>
</x-app-layout>