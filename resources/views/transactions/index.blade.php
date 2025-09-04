<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Transações de {{ $asset->ticker }}
                </h2>
                <a href="{{ route('assets.index') }}" class="text-sm text-blue-500 hover:underline">&larr; Voltar para Meus Ativos</a>
            </div>
            <a href="{{ route('assets.transactions.create', $asset) }}" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">
                Nova Transação
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if (session('success'))
                        <div class="mb-4 p-4 bg-green-100 text-green-700 rounded-md">
                            {{ session('success') }}
                        </div>
                    @endif
                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-white">
                            <thead class="bg-gray-200">
                                <tr>
                                    <th class="py-2 px-4 border-b">Data</th>
                                    <th class="py-2 px-4 border-b">Tipo</th>
                                    <th class="py-2 px-4 border-b text-right">Quantidade</th>
                                    <th class="py-2 px-4 border-b text-right">Preço Unitário (R$)</th>
                                    <th class="py-2 px-4 border-b text-right">Custo Total (R$)</th>
                                    <th class="py-2 px-4 border-b">Ações</th> {{-- NOVA COLUNA --}}
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($transactions as $transaction)
                                    <tr class="hover:bg-gray-50">
                                        <td class="py-2 px-4 border-b text-center">{{ $transaction->transaction_date->format('d/m/Y') }}</td>
                                        <td class="py-2 px-4 border-b text-center font-semibold {{ $transaction->type === 'buy' ? 'text-green-600' : 'text-red-600' }}">
                                            {{ $transaction->type === 'buy' ? 'COMPRA' : 'VENDA' }}
                                        </td>
                                        <td class="py-2 px-4 border-b text-right">{{ number_format($transaction->quantity, 8, ',', '.') }}</td>
                                        <td class="py-2 px-4 border-b text-right">{{ number_format($transaction->unit_price, 2, ',', '.') }}</td>
                                        <td class="py-2 px-4 border-b text-right">{{ number_format($transaction->quantity * $transaction->unit_price, 2, ',', '.') }}</td>
                                        
                                        <td class="py-2 px-4 border-b text-center whitespace-nowrap">
                                            <div class="flex justify-center items-center">
                                                <a href="{{ route('transactions.edit', $transaction) }}" title="Editar Transação" class="p-2 text-indigo-600 hover:text-indigo-900 transition duration-150 ease-in-out">
                                                    {{-- SVG do Heroicon 'Pencil Square' --}}
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                                    </svg>
                                                </a>

                                                <form action="{{ route('transactions.destroy', $transaction) }}" method="POST" class="inline-block" onsubmit="return confirm('Tem certeza que deseja excluir?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" title="Excluir Transação" class="p-2 text-red-600 hover:text-red-900 transition duration-150 ease-in-out">
                                                        {{-- SVG do Heroicon 'Trash' --}}
                                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.134-2.033-2.134H8.033C6.91 2.75 6 3.664 6 4.834v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                                        </svg>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>                                </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="py-4 px-4 text-center text-gray-500">
                                            Nenhuma transação registrada para este ativo.
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
</x-app-layout>