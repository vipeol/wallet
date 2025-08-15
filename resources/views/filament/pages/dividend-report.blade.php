<x-filament-panels::page>
    {{-- Renderiza o filtro que definimos na classe --}}
    <div class="mb-6">
        {{ $this->form }}
    </div>

    {{-- Tabela Dinâmica com Estilos para Dark Mode --}}
    <div class="filament-tables-container overflow-x-auto bg-white dark:bg-gray-800 shadow-sm rounded-lg">
        <table class="filament-tables-table min-w-full text-sm">
            <thead class="bg-gray-50 dark:bg-white/5">
                <tr>
                    <th class="py-3 px-4 text-left font-semibold text-gray-900 dark:text-white">Ativo</th>
                    {{-- Gera as colunas de meses dinamicamente --}}
                    @foreach ($columnMonths as $month)
                        <th class="py-3 px-4 text-right font-semibold text-gray-900 dark:text-white">{{ \Carbon\Carbon::createFromFormat('Y-m', $month)->format('M/y') }}</th>
                    @endforeach
                    <th class="py-3 px-4 text-right font-bold bg-gray-100 dark:bg-white/10 text-gray-900 dark:text-white">Total Ativo</th>
                </tr>
            </thead>
            <tbody class="divide-y whitespace-nowrap dark:divide-white/10">
                @forelse ($pivotedData as $ticker => $monthlyData)
                    <tr class="hover:bg-gray-50 dark:hover:bg-white/5">
                        <td class="py-3 px-4 font-mono font-semibold text-gray-800 dark:text-gray-200">{{ $ticker }}</td>
                        {{-- Itera sobre os meses e exibe o valor correspondente --}}
                        @foreach ($columnMonths as $month)
                            <td class="py-3 px-4 text-right text-gray-600 dark:text-gray-200">
                                {{ number_format($monthlyData[$month] ?? 0, 2, ',', '.') }}
                            </td>
                        @endforeach
                        {{-- Total da Linha --}}
                        <td class="py-3 px-4 text-right font-bold bg-gray-50 dark:bg-white/5 text-gray-800 dark:text-gray-200">
                            {{ number_format(collect($monthlyData)->sum(), 2, ',', '.') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ count($columnMonths) + 2 }}" class="py-4 text-center text-gray-500 dark:text-gray-400">
                            Nenhum provento encontrado para os filtros selecionados.
                        </td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot class="bg-gray-100 dark:bg-white/10 font-bold text-gray-900 dark:text-white">
                <tr>
                    <td class="py-3 px-4 text-left">Total Mês</td>
                    {{-- Itera sobre os meses para exibir o total da coluna --}}
                    @foreach ($columnMonths as $month)
                        <td class="py-3 px-4 text-right">
                            {{ number_format($monthlyTotals[$month] ?? 0, 2, ',', '.') }}
                        </td>
                    @endforeach
                    {{-- Total Geral --}}
                    <td class="py-3 px-4 text-right font-bold bg-gray-50 dark:bg-white/5 text-gray-800 dark:text-gray-200">
                        {{ number_format(collect($monthlyTotals)->sum(), 2, ',', '.') }}
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>

</x-filament-panels::page>