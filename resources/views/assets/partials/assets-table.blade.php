                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-white text-sm">
                            <thead class="bg-gray-200">
                                {{-- O cabeçalho agora tem TODAS as colunas bi-partidas --}}
                                <tr>
                                    <th rowspan="2" class="py-2 px-3 border-b align-middle">Ticker</th>
                                    <th rowspan="2" class="py-2 px-3 border-b align-middle text-right">Posição</th>
                                    <th rowspan="2" class="py-2 px-3 border-b align-middle text-right">Preço Médio</th>
                                    <th rowspan="2" class="py-2 px-3 border-b align-middle text-right">Cotação Atual</th>
                                    <th rowspan="2" class="py-2 px-3 border-b align-middle text-right">Valor Mercado</th>
                                    <th rowspan="2" class="py-2 px-3 border-b align-middle text-right">Custo Total</th>
                                    <th colspan="2" class="py-2 px-3 border-b text-center">Variação Dia</th>
                                    <th colspan="2" class="py-2 px-3 border-b text-center">Lucro/Prejuízo</th>
                                    <th colspan="2" class="py-2 px-3 border-b text-center">Proventos</th>
                                    <th rowspan="2" class="py-2 px-3 border-b align-middle">Ações</th>
                                </tr>
                                <tr>
                                    <th class="py-2 px-3 border-b font-semibold bg-gray-100 text-right">R$</th>
                                    <th class="py-2 px-3 border-b font-semibold bg-gray-100 text-right">%</th>
                                    <th class="py-2 px-3 border-b font-semibold bg-gray-100 text-right">R$</th>
                                    <th class="py-2 px-3 border-b font-semibold bg-gray-100 text-right">%</th>
                                    <th class="py-2 px-3 border-b font-semibold bg-gray-100 text-right">R$</th>
                                    <th class="py-2 px-3 border-b font-semibold bg-gray-100 text-right">YoC</th>
                                </tr>
                            </thead>
                            <tbody class="border-t-4 border-gray-300">
                            
                                @forelse ($assets as $asset)
                                        <tr class="hover:bg-gray-50">
                                            <td class="py-2 px-3 border-b font-mono">
                                                <div class="flex items-center">
                                                    @if ($asset->logo_path)
                                                        <img src="{{ $asset->logo_path }}" alt="Logo de {{ $asset->ticker }}" class="w-6 h-6 mr-3 rounded-full">
                                                    @else
                                                        <div class="w-6 h-6 mr-3 rounded-full bg-gray-200 flex items-center justify-center text-xs font-bold text-gray-500">{{ substr($asset->ticker, 0, 2) }}</div>
                                                    @endif
                                                    <span>
                                                        {{ $asset->ticker }}
                                                        @if($asset->currency === 'USD')
                                                            <span class="ml-2 text-xs text-gray-400">(USD)</span>
                                                        @endif
                                                    </span>
                                                </div>
                                            </td>
                                            <td class="py-2 px-3 border-b text-right font-semibold">
                                                @if($asset->type == 'crypto')
                                                    {{ number_format($asset->current_quantity, 8, ',', '.') }}
                                                @else
                                                    {{ number_format($asset->current_quantity, 2, ',', '.') }}
                                                @endif
                                            </td>
                                            <td class="py-2 px-3 border-b text-right">{{ number_format($asset->average_price, 2, ',', '.') }}</td>
                                            <td class="py-2 px-3 border-b text-right">{{ number_format($asset->latest_price, 2, ',', '.') }}</td>
                                            <td class="py-2 px-3 border-b text-right font-bold">{{ number_format($asset->market_value, 2, ',', '.') }}</td>
                                            <td class="py-2 px-3 border-b text-right">{{ number_format($asset->total_acquisition_cost, 2, ',', '.') }}</td>
                                            {{-- Variação do Dia --}}
                                            @php $dayPlColor = $asset->day_profit_loss >= 0 ? 'text-green-600' : 'text-red-600'; @endphp
                                            <td class="py-2 px-3 border-b text-right font-semibold {{ $dayPlColor }}">{{ number_format($asset->day_profit_loss, 2, ',', '.') }}</td>
                                            <td class="py-2 px-3 border-b text-right font-semibold {{ $dayPlColor }}">{{ number_format($asset->day_profit_loss_percentage, 2, ',', '.') }}%</td>

                                            {{-- Lucro/Prejuízo Total --}}
                                            @php $totalPlColor = $asset->unrealized_profit_loss >= 0 ? 'text-green-600' : 'text-red-600'; @endphp
                                            <td class="py-2 px-3 border-b text-right font-semibold {{ $totalPlColor }}">{{ number_format($asset->unrealized_profit_loss, 2, ',', '.') }}</td>
                                            <td class="py-2 px-3 border-b text-right font-semibold {{ $totalPlColor }}">{{ number_format($asset->unrealized_profit_loss_percentage, 2, ',', '.') }}%</td>
                                            
                                            {{-- Proventos --}}
                                            <td class="py-2 px-3 border-b text-right text-blue-700">{{ number_format($asset->total_dividends_received, 2, ',', '.') }}</td>
                                            <td class="py-2 px-3 border-b text-right text-blue-700 font-semibold">{{ number_format($asset->yield_on_cost, 2, ',', '.') }}%</td>

                                            {{-- Esta é a célula (<td>) da coluna "Ações" que vamos substituir --}}
                                            <td class="py-2 px-3 border-b text-center whitespace-nowrap">
                                                
                                                {{-- Container do Menu com Alpine.js --}}
                                                <div x-data="{ open: false }" @click.outside="open = false" class="relative inline-block text-left">
                                                    
                                                    {{-- Botão de 3 pontos (kebab menu) --}}
                                                    <div>
                                                        <button @click="open = !open" type="button" class="inline-flex items-center justify-center w-full rounded-md p-2 text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-gray-100 focus:ring-indigo-500" id="menu-button" aria-expanded="true" aria-haspopup="true">
                                                            <span class="sr-only">Opções</span>
                                                            {{-- SVG do Heroicon 'ellipsis-vertical' --}}
                                                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                                <path d="M10 3a1.5 1.5 0 110 3 1.5 1.5 0 010-3zM10 8.5a1.5 1.5 0 110 3 1.5 1.5 0 010-3zM11.5 15.5a1.5 1.5 0 10-3 0 1.5 1.5 0 003 0z" />
                                                            </svg>
                                                        </button>
                                                    </div>

                                                    {{-- Painel do Menu Suspenso --}}
                                                    <div x-show="open"
                                                        x-transition:enter="transition ease-out duration-100"
                                                        x-transition:enter-start="transform opacity-0 scale-95"
                                                        x-transition:enter-end="transform opacity-100 scale-100"
                                                        x-transition:leave="transition ease-in duration-75"
                                                        x-transition:leave-start="transform opacity-100 scale-100"
                                                        x-transition:leave-end="transform opacity-0 scale-95"
                                                        class="absolute right-0 z-10 mt-2 w-56 origin-top-right rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none"
                                                        role="menu" aria-orientation="vertical" aria-labelledby="menu-button" tabindex="-1"
                                                        style="display: none;">
                                                        <div class="py-1" role="none">
                                                            
                                                            <a href="{{ route('assets.dividends.index', $asset) }}" class="text-green-700 block px-4 py-2 text-sm hover:bg-gray-100" role="menuitem" tabindex="-1">
                                                                <div class="flex items-center">
                                                                    {{-- SVG do Heroicon 'banknotes' --}}
                                                                    <svg class="w-5 h-5 mr-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" /></svg>
                                                                    <span>Ver Proventos</span>
                                                                </div>
                                                            </a>
                                                            
                                                            <a href="{{ route('assets.transactions.index', $asset) }}" class="text-gray-700 block px-4 py-2 text-sm hover:bg-gray-100" role="menuitem" tabindex="-1">
                                                                <div class="flex items-center">
                                                                    {{-- SVG do Heroicon 'Arrows Right Left' --}}
                                                                    <svg class="w-5 h-5 mr-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21 3 16.5m0 0L7.5 12M3 16.5h18m-7.5-1.5L21 7.5m0 0L16.5 3M21 7.5H3" /></svg>
                                                                    <span>Ver Transações</span>
                                                                </div>
                                                            </a>
                                                            
                                                            <a href="{{ route('assets.corporate-actions.index', $asset) }}" class="text-gray-700 block px-4 py-2 text-sm hover:bg-gray-100" role="menuitem" tabindex="-1">
                                                                <div class="flex items-center">
                                                                    {{-- SVG do Heroicon 'cog-6-tooth' --}}
                                                                    <svg class="w-5 h-5 mr-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.24-.438.613-.438.995s.145.755.438.995l1.003.827c.446.368.521 1.04.164 1.431l-1.296 2.247a1.125 1.125 0 0 1-1.37.49l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.063-.374-.313-.686-.645-.87a6.52 6.52 0 0 1-.22-.127c-.324-.196-.72-.257-1.075-.124l-1.217.456a1.125 1.125 0 0 1-1.37-.49l-1.296-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.437-.995s-.145-.755-.438-.995l-1.004-.827a1.125 1.125 0 0 1-.164-1.431l1.296-2.247a1.125 1.125 0 0 1 1.37-.49l1.217.456c.355.133.75.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                                                                    <span>Eventos Corporativos</span>
                                                                </div>
                                                            </a>

                                                            <a href="{{ route('assets.edit', $asset) }}" class="text-indigo-700 block px-4 py-2 text-sm hover:bg-gray-100" role="menuitem" tabindex="-1">
                                                                <div class="flex items-center">
                                                                    {{-- SVG do Heroicon 'Pencil Square' --}}
                                                                    <svg class="w-5 h-5 mr-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" /></svg>
                                                                    <span>Editar Ativo</span>
                                                                </div>
                                                            </a>

                                                            <form method="POST" action="{{ route('assets.destroy', $asset) }}" role="none" onsubmit="return confirm('Tem certeza que deseja excluir?');">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="text-red-700 block w-full hover:bg-gray-100" role="menuitem" tabindex="-1">
                                                                    <div class="flex items-center px-4 py-2 text-sm">
                                                                        {{-- SVG do Heroicon 'Trash' --}}
                                                                        <svg class="w-5 h-5 mr-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.134-2.033-2.134H8.033C6.91 2.75 6 3.664 6 4.834v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                                                                        <span>Excluir Ativo</span>
                                                                    </div>
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>                                            
                                        </tr>
                                @empty
                                    <tr><td colspan="12" class="py-4 px-4 text-center text-gray-500">Nenhum ativo nesta carteira.</td></tr>
                                @endforelse
                            </tbody>

                            {{-- LINHA DE TOTAL GERAL --}}
                            <tfoot class="bg-gray-50">
                                <tr>
                                    <td colspan="4" class="py-2 px-3 border-b text-right font-bold">Subtotal:</td>
                                    <td class="py-2 px-3 border-b text-right font-extrabold">{{ number_format($assets->sum('market_value'), 2, ',', '.') }}</td>
                                    <td class="py-2 px-3 border-b text-right font-extrabold">{{ number_format($assets->sum('total_acquisition_cost'), 2, ',', '.') }}</td>
                                    <td class="py-2 px-3 border-b text-right font-extrabold">{{ number_format($assets->sum('day_profit_loss'), 2, ',', '.') }}</td>
                                    <td class="py-2 px-3 border-b"></td>
                                    <td class="py-2 px-3 border-b text-right font-extrabold">{{ number_format($assets->sum('unrealized_profit_loss'), 2, ',', '.') }}</td>
                                    <td class="py-2 px-3 border-b"></td>
                                    <td class="py-2 px-3 border-b text-right font-extrabold text-blue-700">{{ number_format($assets->sum('total_dividends_received'), 2, ',', '.') }}</td>
                                    <td colspan="2" class="py-2 px-3 border-b"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>