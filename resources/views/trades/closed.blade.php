<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Trades Encerrados') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-white text-sm">
                            <thead class="bg-gray-200">
                                <tr>
                                    <th class="py-2 px-3 border-b text-left">Ativo</th>
                                    <th class="py-2 px-3 border-b text-right">Preço Médio Custo (R$)</th>
                                    <th class="py-2 px-3 border-b text-right">Preço Médio Venda (R$)</th>
                                    <th class="py-2 px-3 border-b text-right">Lucro / Prejuízo Realizado (R$)</th>
                                    <th class="py-2 px-3 border-b text-center">Ações</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                @forelse ($closedAssets as $asset)
                                    <tr class="hover:bg-gray-50">
                                        <td class="py-2 px-3 border-b font-mono">
                                            <div class="flex items-center">
                                                @if ($asset->logo_path)
                                                    <img src="{{ $asset->logo_path }}" alt="Logo de {{ $asset->ticker }}" class="w-6 h-6 mr-3 rounded-full">
                                                @else
                                                    <div class="w-6 h-6 mr-3 rounded-full bg-gray-200 flex items-center justify-center text-xs font-bold text-gray-500">{{ substr($asset->ticker, 0, 2) }}</div>
                                                @endif
                                                <span>{{ $asset->ticker }}</span>
                                            </div>
                                        </td>
                                        {{-- NOVA COLUNA: Preço Médio Custo --}}
                                        <td class="py-2 px-3 border-b text-right">
                                            {{ number_format($asset->historical_average_buy_price, 2, ',', '.') }}
                                        </td>
                                        {{-- NOVA COLUNA: Preço Médio Venda --}}
                                        <td class="py-2 px-3 border-b text-right">
                                            {{ number_format($asset->average_sell_price, 2, ',', '.') }}
                                        </td>
                                        @php
                                            $plColor = $asset->realized_profit_loss >= 0 ? 'text-green-600' : 'text-red-600';
                                        @endphp
                                        <td class="py-2 px-3 border-b text-right font-semibold {{ $plColor }}">
                                            {{ number_format($asset->realized_profit_loss, 2, ',', '.') }}
                                        </td>
                                        <td class="py-2 px-3 border-b text-center">
                                            <form action="{{ route('assets.destroy', $asset) }}" method="POST" class="inline-block" onsubmit="return confirm('Tem certeza que deseja excluir permanentemente este ativo e todo o seu histórico? Esta ação não pode ser desfeita.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" title="Excluir Ativo Permanentemente" class="p-2 text-red-600 hover:text-red-900 transition duration-150 ease-in-out">
                                                    {{-- SVG do Heroicon 'Trash' --}}
                                                    <svg xmlns="http://www.w.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.134-2.033-2.134H8.033C6.91 2.75 6 3.664 6 4.834v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                                                </button>
                                            </form>
                                        </td>                                    
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="py-4 text-center text-gray-500">Nenhum trade encerrado encontrado.</td></tr>
                                @endforelse
                            </tbody>
                            {{-- NOVO RODAPÉ COM O SOMATÓRIO --}}
                            <tfoot class="bg-gray-200 font-bold">
                                <tr>
                                    <td colspan="4" class="py-3 px-4 text-right">Total Realizado:</td>
                                    @php
                                        $grandTotalPl = $closedAssets->sum('realized_profit_loss');
                                        $grandTotalPlColor = $grandTotalPl >= 0 ? 'text-green-700' : 'text-red-700';
                                    @endphp
                                    <td class="py-3 px-4 text-right text-lg {{ $grandTotalPlColor }}">
                                        R$ {{ number_format($grandTotalPl, 2, ',', '.') }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>