<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:text-gray-200">
            {{ __('Eventos Corporativos') }} para {{ $asset->ticker }}
        </h2>
    </x-slot>

    {{-- ADICIONE TODA ESTA SEÇÃO DE HISTÓRICO --}}
    <div class="py-3">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Histórico de Eventos Aplicados</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-white text-sm">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="py-2 px-3 border-b text-left">Data do Evento</th>
                                    <th class="py-2 px-3 border-b text-left">Tipo</th>
                                    <th class="py-2 px-3 border-b text-left">Detalhes</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($history as $action)
                                    <tr class="hover:bg-gray-50">
                                        <td class="py-2 px-3 border-b">{{ $action->action_date->format('d/m/Y') }}</td>
                                        <td class="py-2 px-3 border-b font-semibold">
                                            @switch($action->type)
                                                @case('split') Desdobramento @break
                                                @case('reverse_split') Grupamento @break
                                                @case('ticker_change') Mudança de Ticker @break
                                            @endswitch
                                        </td>
                                        <td class="py-2 px-3 border-b">
                                            @switch($action->type)
                                                @case('split')
                                                @case('reverse_split')
                                                    Proporção: De {{ $action->details['from'] }} para {{ $action->details['to'] }}
                                                    @break
                                                @case('ticker_change')
                                                    Ticker alterado: De {{ $action->details['from'] }} para {{ $action->details['to'] }}
                                                    @break
                                            @endswitch
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="py-4 text-center text-gray-500">
                                            Nenhum evento corporativo registrado para este ativo.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- FIM DA SEÇÃO DE HISTÓRICO --}}

    <div class="py-3">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('success'))
                <div class="p-4 bg-green-100 text-green-700 rounded-md">{{ session('success') }}</div>
            @endif
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900">Desdobramento (Split)</h3>
                <p class="mt-1 text-sm text-gray-600">
                    Aumenta o número de ações, diminuindo o preço. Exemplo (2 para 1): De 1 Para 2.
                </p>
                <form action="{{ route('assets.corporate-actions.store', $asset) }}" method="POST" class="mt-4 space-y-4">
                    @csrf
                    <input type="hidden" name="type" value="split">
                    <div>
                        <x-input-label for="action_date_split" value="Data Ex do Evento" />
                        <x-text-input id="action_date_split" name="action_date" type="date" class="mt-1 block w-full" required />
                    </div>
                    <div class="flex items-center space-x-4">
                        <div>
                            <x-input-label for="split_from" value="De (Proporção)" />
                            <x-text-input id="split_from" name="split_from" type="number" class="mt-1 block w-full" required />
                        </div>
                        <div>
                            <x-input-label for="split_to" value="Para (Proporção)" />
                            <x-text-input id="split_to" name="split_to" type="number" class="mt-1 block w-full" required />
                        </div>
                    </div>
                    <x-primary-button>Aplicar Desdobramento</x-primary-button>
                </form>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900">Grupamento (Reverse Split)</h3>
                <p class="mt-1 text-sm text-gray-600">
                    Diminui o número de ações, aumentando o preço. Exemplo (1 para 10): De 10 Para 1.
                </p>
                <form action="{{ route('assets.corporate-actions.store', $asset) }}" method="POST" class="mt-4 space-y-4">
                    @csrf
                    <input type="hidden" name="type" value="reverse_split">
                    <div>
                        <x-input-label for="action_date_reverse" value="Data Ex do Evento" />
                        <x-text-input id="action_date_reverse" name="action_date" type="date" class="mt-1 block w-full" required />
                    </div>
                    <div class="flex items-center space-x-4">
                        <div>
                            <x-input-label for="split_from_reverse" value="De (Proporção)" />
                            <x-text-input id="split_from_reverse" name="split_from" type="number" class="mt-1 block w-full" required />
                        </div>
                        <div>
                            <x-input-label for="split_to_reverse" value="Para (Proporção)" />
                            <x-text-input id="split_to_reverse" name="split_to" type="number" class="mt-1 block w-full" required />
                        </div>
                    </div>
                    <x-primary-button>Aplicar Grupamento</x-primary-button>
                </form>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900">Mudança de Ticker</h3>
                <form action="{{ route('assets.corporate-actions.store', $asset) }}" method="POST" class="mt-4 space-y-4">
                    @csrf
                    <input type="hidden" name="type" value="ticker_change">
                    <div>
                        <x-input-label for="action_date_ticker" value="Data da Mudança" />
                        <x-text-input id="action_date_ticker" name="action_date" type="date" class="mt-1 block w-full" required />
                    </div>
                    <div>
                        <x-input-label for="new_ticker" value="Novo Ticker" />
                        <x-text-input id="new_ticker" name="new_ticker" type="text" class="mt-1 block w-full uppercase" required />
                    </div>
                    <x-primary-button>Alterar Ticker</x-primary-button>
                </form>
            </div>
        </div>
    </div>

</x-app-layout>