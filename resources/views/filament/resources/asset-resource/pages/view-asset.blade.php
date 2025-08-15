<x-filament-panels::page>
    {{-- Seção de Resumo e Gráfico --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Card de Informações Principais --}}
        <div class="lg:col-span-1 bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
            <div class="flex items-center mb-4">
                @if ($record->logo_path)
                    <img src="{{ $record->logo_path }}" alt="Logo de {{ $record->ticker }}" class="w-12 h-12 mr-4 rounded-full">
                @endif
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $record->ticker }}</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $record->name }}</p>
                </div>
            </div>
            
            <dl class="space-y-4">
                <div class="flex justify-between">
                    <dt class="text-gray-500 dark:text-gray-400">Valor de Mercado</dt>
                    <dd class="font-semibold text-gray-900 dark:text-white">R$ {{ number_format($record->market_value, 2, ',', '.') }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500 dark:text-gray-400">Lucro/Prejuízo</dt>
                    <dd class="font-semibold {{ $record->unrealized_profit_loss >= 0 ? 'text-green-600' : 'text-red-600' }}">R$ {{ number_format($record->unrealized_profit_loss, 2, ',', '.') }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500 dark:text-gray-400">Posição Atual</dt>
                    <dd class="font-semibold text-gray-900 dark:text-white">{{ number_format($record->current_quantity, 2, ',', '.') }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500 dark:text-gray-400">Preço Médio</dt>
                    <dd class="font-semibold text-gray-900 dark:text-white">R$ {{ number_format($record->average_price, 2, ',', '.') }}</dd>
                </div>
            </dl>
        </div>

        {{-- Card do Gráfico --}}
        <div class="lg:col-span-2 bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Histórico de Cotações</h3>
            <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
            <div x-data="{
                chart: null,
                initChart: function() {
                    const data = {{ json_encode($this->getChartData()) }};
                    this.chart = new Chart($refs.canvas, {
                        type: 'line',
                        data: data,
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: { legend: { display: false } },
                            scales: {
                                y: { ticks: { callback: (value) => 'R$ ' + value.toLocaleString('pt-BR') } }
                            }
                        }
                    });
                }
            }" x-init="initChart()">
                <canvas x-ref="canvas" style="height: 250px;"></canvas>
            </div>
        </div>
    </div>

    {{-- Seção de Tabelas (Transações e Proventos) --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
        {{-- Tabela de Transações --}}
        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Últimas Transações</h3>
            <div class="overflow-y-auto" style="max-height: 300px;">
                <table class="min-w-full text-sm">
                    <tbody>
                        @forelse ($this->getTransactions() as $transaction)
                            <tr class="border-b dark:border-gray-700">
                                <td class="py-2">{{ $transaction->transaction_date->format('d/m/Y') }}</td>
                                <td class="font-semibold {{ $transaction->type === 'buy' ? 'text-green-600' : 'text-red-600' }}">{{ $transaction->type === 'buy' ? 'COMPRA' : 'VENDA' }}</td>
                                <td class="text-right">{{ number_format($transaction->quantity, 2, ',', '.') }} @ R$ {{ number_format($transaction->unit_price, 2, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr><td class="py-2 text-gray-500">Nenhuma transação.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Tabela de Proventos --}}
        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Últimos Proventos</h3>
            <div class="overflow-y-auto" style="max-height: 300px;">
                <table class="min-w-full text-sm">
                    <tbody>
                        @forelse ($this->getDividends() as $dividend)
                            <tr class="border-b dark:border-gray-700">
                                <td class="py-2">{{ $dividend->payment_date->format('d/m/Y') }}</td>
                                <td class="font-semibold text-blue-600">{{ $dividend->type }}</td>
                                <td class="text-right">R$ {{ number_format($dividend->amount_per_share, 4, ',', '.') }} por cota</td>
                            </tr>
                        @empty
                            <tr><td class="py-2 text-gray-500">Nenhum provento.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</x-filament-panels::page>