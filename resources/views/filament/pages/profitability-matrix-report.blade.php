<x-filament-panels::page>
    <div class="mb-6">
        {{ $this->form }}
    </div>

    @forelse ($reportData as $portfolioName => $yearlyData)
        <div class="mb-8">
            <h3 class="text-2xl font-bold text-gray-800 dark:text-gray-200 mb-4">{{ $portfolioName }}</h3>
            <div class="filament-tables-container overflow-x-auto bg-white dark:bg-gray-800 shadow-sm rounded-lg">
                <table class="filament-tables-table min-w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-white/5">
                        <tr>
                            <th class="py-3 px-4 text-left font-semibold text-gray-900 dark:text-white">Ano</th>
                            @foreach (['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'] as $month)
                                <th class="py-3 px-4 text-right font-semibold text-gray-900 dark:text-white">{{ $month }}</th>
                            @endforeach
                            <th class="py-3 px-4 text-right font-bold bg-gray-100 dark:bg-white/10 text-gray-900 dark:text-white">Total Ano</th>
                            <th class="py-3 px-4 text-right font-bold bg-gray-100 dark:bg-white/10 text-gray-900 dark:text-white">Acumulado</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y whitespace-nowrap dark:divide-white/10">
                        @foreach ($yearlyData as $year => $returns)
                            <tr class="hover:bg-gray-50 dark:hover:bg-white/5">
                                <td class="py-3 px-4 font-semibold text-gray-800 dark:text-gray-200">{{ $year }}</td>
                                {{-- Loop @for garante que as 12 colunas sejam sempre renderizadas --}}
                                @for ($m = 1; $m <= 12; $m++)
                                    @php
                                        $monthlyReturn = $returns['months'][$m] ?? null;
                                        $color = 'text-gray-500 dark:text-gray-400';
                                        if (is_numeric($monthlyReturn)) {
                                            $color = $monthlyReturn >= 0 ? 'text-green-600' : 'text-red-600';
                                        }
                                    @endphp
                                    <td class="py-3 px-4 text-right font-mono {{ $color }}">
                                        {!! is_numeric($monthlyReturn) ? number_format($monthlyReturn, 2, ',', '.') . '%' : '-' !!}
                                    </td>
                                @endfor
                                <td class="py-3 px-4 text-right font-bold bg-gray-50 dark:bg-white/5 {{ ($returns['total_year'] ?? 0) >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                    {{ number_format($returns['total_year'] ?? 0, 2, ',', '.') }}%
                                </td>
                                <td class="py-3 px-4 text-right font-bold bg-gray-50 dark:bg-white/5 {{ ($returns['accumulated'] ?? 0) >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                    {{ number_format($returns['accumulated'] ?? 0, 2, ',', '.') }}%
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @empty
        <div class="p-6 bg-white dark:bg-gray-800 shadow-sm rounded-lg text-center text-gray-500">
            Nenhum dado de rentabilidade encontrado. Rode o comando `php artisan report:profitability` primeiro.
        </div>
    @endforelse
</x-filament-panels::page>