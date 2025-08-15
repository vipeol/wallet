<x-filament-panels::page>
    <div class="mb-6">
        {{ $this->form }}
    </div>

    <div class="filament-tables-container overflow-x-auto bg-white dark:bg-gray-800 shadow-sm rounded-lg">
        <table class="filament-tables-table min-w-full text-sm">
            <thead class="bg-gray-50 dark:bg-white/5">
                <tr>
                    <th class="py-3 px-4 text-left font-semibold text-gray-900 dark:text-white">Ano</th>
                    @for ($i = 1; $i <= 12; $i++)
                        <th class="py-3 px-4 text-right font-semibold text-gray-900 dark:text-white">
                            {{ \Carbon\Carbon::create()->month($i)->translatedFormat('M') }}
                        </th>
                    @endfor
                    <th class="py-3 px-4 text-right font-bold bg-gray-100 dark:bg-white/10 text-gray-900 dark:text-white">Total Ano</th>
                    <th class="py-3 px-4 text-right font-bold bg-gray-100 dark:bg-white/10 text-gray-900 dark:text-white">Média Mês</th>
                </tr>
            </thead>
            <tbody class="divide-y whitespace-nowrap dark:divide-white/10">
                @forelse ($pivotedData as $year => $monthlyData)
                    <tr class="hover:bg-gray-50 dark:hover:bg-white/5">
                        <td class="py-3 px-4 font-semibold text-gray-800 dark:text-gray-200">{{ $year }}</td>
                        {{-- Loop para cada um dos 12 meses --}}
                        @for ($i = 1; $i <= 12; $i++)
                            @php
                                // Formata o mês com zero à esquerda (01, 02, etc.) para buscar no array
                                $monthKey = str_pad($i, 2, '0', STR_PAD_LEFT);
                                $value = $monthlyData[$monthKey] ?? 0;
                            @endphp
                            <td class="py-3 px-4 text-right text-gray-800 dark:text-gray-300">
                                {{-- Exibe 0.00 se não houver proventos no mês --}}
                                @if ($value > 0)
                                    {{ number_format($value, 2, ',', '.') }}
                                @else
                                    -
                                @endif
                            </td>
                        @endfor
                        
                        {{-- Coluna com a soma de todos os meses do ano --}}
                        @php
                            $yearlyTotal = collect($monthlyData)->sum();
                        @endphp
                        <td class="py-3 px-4 text-right font-bold bg-gray-50 dark:bg-white/5 text-gray-800 dark:text-gray-200">
                            {{ number_format($yearlyTotal, 2, ',', '.') }}
                        </td>
                        
                        {{-- Coluna com a média do ano (considerando apenas meses com pagamento) --}}
                        <td class="py-3 px-4 text-right font-bold bg-gray-50 dark:bg-white/5 text-gray-800 dark:text-gray-200">
                            @php
                                $payingMonths = count(array_filter($monthlyData));
                                $yearlyAverage = $payingMonths > 0 ? $yearlyTotal / $payingMonths : 0;
                            @endphp
                            {{ number_format($yearlyAverage, 2, ',', '.') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="15" class="py-4 text-center text-gray-500 dark:text-gray-400">
                            Nenhum provento encontrado para os filtros selecionados.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-filament-panels::page>