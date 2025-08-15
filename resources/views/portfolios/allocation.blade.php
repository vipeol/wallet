<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Alocação por Carteira') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @forelse ($portfolios as $portfolio)
                <div class="mb-8 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex justify-between items-baseline mb-4">
                            <h3 class="text-2xl font-bold text-gray-800">{{ $portfolio->name }}</h3>
                            <p class="text-lg font-semibold text-gray-600">
                                Valor Total: R$ {{ number_format($portfolio->total_market_value, 2, ',', '.') }}
                            </p>
                        </div>

                        <table class="min-w-full bg-white text-sm">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="py-2 px-3 border-b text-left">Ativo</th>
                                    <th class="py-2 px-3 border-b text-right">Percentual Objetivo (%)</th>
                                    <th class="py-2 px-3 border-b text-right">Percentual Atual (%)</th>
                                    <th class="py-2 px-3 border-b text-right">Diferença (%)</th>
                                    <th class="py-2 px-3 border-b text-right">Ajuste Necessário (R$)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($portfolio->assets->whereNotIn('type', ['currency', 'benchmark']) as $asset)
                                    <tr class="hover:bg-gray-50">
                                        <td class="py-2 px-3 border-b font-mono">{{ $asset->ticker }}</td>
                                        <td class="py-2 px-3 border-b text-right font-semibold">{{ number_format($asset->target_percentage, 2, ',', '.') }}%</td>
                                        <td class="py-2 px-3 border-b text-right">{{ number_format($asset->current_portfolio_percentage, 2, ',', '.') }}%</td>
                                        <td class="py-2 px-3 border-b text-right">{{ number_format($asset->target_percentage - $asset->current_portfolio_percentage, 2, ',', '.') }}%</td>
                                        @php
                                            $adjustment = $asset->adjustment_needed;
                                            $adjustmentColor = $adjustment >= 0 ? 'text-green-600' : 'text-red-600';
                                        @endphp
                                        <td class="py-2 px-3 border-b text-right font-bold {{ $adjustmentColor }}">
                                            {{ $adjustment >= 0 ? 'Comprar' : 'Vender' }} R$ {{ number_format(abs($adjustment), 2, ',', '.') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-gray-100 font-bold">
                                <tr>
                                    <td class="py-2 px-3 border-b text-right">Total:</td>
                                    <td class="py-2 px-3 border-b text-right">{{ number_format($portfolio->assets->sum('target_percentage'), 2, ',', '.') }}%</td>
                                    <td class="py-2 px-3 border-b text-right">{{ number_format($portfolio->assets->sum('current_portfolio_percentage'), 2, ',', '.') }}%</td>
                                    <td colspan="2" class="py-2 px-3 border-b"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            @empty
                <p class="text-center text-gray-500">Nenhuma carteira encontrada.</p>
            @endforelse
        </div>
    </div>
</x-app-layout>