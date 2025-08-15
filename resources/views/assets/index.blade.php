<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Meus Ativos') }}
            </h2>
            <div class="flex space-x-4">
                <a href="{{ route('portfolios.allocation') }}" class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600">
                    Ver Alocação
                </a>
                <a href="{{ route('assets.create') }}" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">
                    Novo Ativo
                </a>
            </div>
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

                    {{-- Loop para cada Carteira --}}
                    @foreach ($portfolios as $portfolio)
                        <div class="mb-8">
                            <h3 class="text-2xl font-bold text-gray-800 mb-4">{{ $portfolio->name }}</h3>
                            
                            {{-- Inclui a tabela de ativos, passando os ativos desta carteira --}}
                            @include('assets.partials.assets-table', ['assets' => $portfolio->assets])
                        </div>
                    @endforeach

                    {{-- Seção para Ativos Sem Carteira --}}
                    @if($assetsWithoutPortfolio->isNotEmpty())
                        <div class="mb-8">
                            <h3 class="text-2xl font-bold text-gray-800 mb-4">Ativos Sem Carteira</h3>
                            @include('assets.partials.assets-table', ['assets' => $assetsWithoutPortfolio])
                        </div>
                    @endif                

                    {{-- SEÇÃO DE TOTAL GERAL --}}
                    <div class="mt-12">
                        <h3 class="text-2xl font-bold text-gray-800 mb-4">Total Geral da Carteira</h3>
                        <div class="bg-gray-200 p-4 rounded-lg">
                            <dl class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                <div class="text-center">
                                    <dt class="text-sm font-medium text-gray-500">Valor de Mercado Total</dt>
                                    <dd class="mt-1 text-2xl font-semibold tracking-tight text-gray-900">R$ {{ number_format($grandTotalMarketValue, 2, ',', '.') }}</dd>
                                </div>
                                <div class="text-center">
                                    <dt class="text-sm font-medium text-gray-500">Variação do Dia</dt>
                                    <dd class="mt-1 text-2xl font-semibold tracking-tight {{ $grandTotalDayProfitLoss >= 0 ? 'text-green-600' : 'text-red-600' }}">R$ {{ number_format($grandTotalDayProfitLoss, 2, ',', '.') }}</dd>
                                </div>
                                <div class="text-center">
                                    <dt class="text-sm font-medium text-gray-500">Lucro/Prejuízo Total</dt>
                                    <dd class="mt-1 text-2xl font-semibold tracking-tight {{ $grandTotalUnrealizedProfitLoss >= 0 ? 'text-green-600' : 'text-red-600' }}">R$ {{ number_format($grandTotalUnrealizedProfitLoss, 2, ',', '.') }}</dd>
                                </div>
                                <div class="text-center">
                                    <dt class="text-sm font-medium text-gray-500">Proventos Recebidos</dt>
                                    <dd class="mt-1 text-2xl font-semibold tracking-tight text-blue-700">R$ {{ number_format($grandTotalDividendsReceived, 2, ',', '.') }}</dd>
                                </div>
                            </dl>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>