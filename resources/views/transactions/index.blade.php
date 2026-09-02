<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('💳 Mes Transactions') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <!-- Bouton Faire une Transaction-->
            <div class="flex justify-end mb-6">
                <a href="{{ route('transactions.create') }}" class="bg-green-600 hover:bg-green-700 text-white font-semibold px-6 py-2 rounded-lg shadow-md transition duration-300">
                    + Faire une Transaction
                </a>
            </div>

            <!-- Barre de recherche -->
            <div
                x-data="searchSuggestions('{{ route('produits.suggestions') }}', {{ \Illuminate\Support\Js::from($search ?? '') }})"
                @click.outside="open = false"
                class="relative mb-6"
            >
                <form method="GET" action="{{ route('transactions.index') }}" x-ref="form" class="flex gap-2">
                    <input type="hidden" name="sort" value="{{ $sort }}">
                    <input type="hidden" name="direction" value="{{ $direction }}">
                    <div class="flex-1 relative">
                        <input
                            type="text"
                            name="search"
                            x-model="query"
                            @input="search()"
                            @keydown.escape="open = false"
                            autocomplete="off"
                            placeholder="Rechercher par nom de produit..."
                            class="w-full rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        >
                        <ul
                            x-show="open"
                            class="absolute z-10 mt-1 w-full bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md shadow-lg max-h-56 overflow-auto"
                            style="display: none;"
                        >
                            <template x-for="suggestion in suggestions" :key="suggestion">
                                <li @click="select(suggestion)" class="px-4 py-2 hover:bg-blue-50 dark:hover:bg-gray-600 cursor-pointer" x-text="suggestion"></li>
                            </template>
                        </ul>
                    </div>
                    <button type="submit" class="bg-gray-700 hover:bg-gray-800 text-white px-4 py-2 rounded-md shadow-md transition duration-300">
                        🔍 Rechercher
                    </button>
                    @if($search)
                        <a href="{{ route('transactions.index', array_filter(['sort' => $sort, 'direction' => $direction])) }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-md shadow-md transition duration-300">
                            Effacer
                        </a>
                    @endif
                </form>
            </div>

         <!--Message de succès-->
            @if(session('success'))
                <div class="bg-green-500 text-white p-3 rounded-lg shadow-md mb-4">
                    {{ session('success') }}
                </div>
            @endif

            @php
                $sortLink = function (string $column) use ($sort, $direction, $search) {
                    $newDirection = ($sort === $column && $direction === 'asc') ? 'desc' : 'asc';
                    return route('transactions.index', array_filter([
                        'search' => $search,
                        'sort' => $column,
                        'direction' => $newDirection,
                    ]));
                };
                $sortIndicator = fn (string $column) => $sort === $column ? ($direction === 'asc' ? '▲' : '▼') : '';
            @endphp

           <!-- Tableau des transactions-->
            <div class="bg-white dark:bg-gray-800 shadow-lg rounded-lg overflow-hidden">
                <div class="hidden sm:block">
                    <table class="w-full border-collapse">
                        <thead class="bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-200 uppercase text-sm font-semibold">
                            <tr class="text-center">
                                <th class="px-6 py-3">Produit</th>
                                <th class="px-6 py-3">
                                    <a href="{{ $sortLink('quantite') }}" class="hover:underline">Quantité {{ $sortIndicator('quantite') }}</a>
                                </th>
                                <th class="px-6 py-3">
                                    <a href="{{ $sortLink('total') }}" class="hover:underline">Prix_Total {{ $sortIndicator('total') }}</a>
                                </th>
                                <th class="px-6 py-3">
                                    <a href="{{ $sortLink('statut') }}" class="hover:underline">Statut {{ $sortIndicator('statut') }}</a>
                                </th>
                                <th class="px-6 py-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-600">
                            @forelse ($transactions as $transaction)
                                <tr class="hover:bg-gray-100 dark:hover:bg-gray-700 transition duration-200 text-center">
                                    <td class="px-6 py-4 font-medium text-gray-800 dark:text-gray-300">
                                        {{ $transaction->produit->nom }}
                                    </td>
                                    <td class="px-6 py-4">{{ $transaction->quantite }}</td>
                                    <td class="px-6 py-4 font-semibold text-blue-500">
                                        {{ number_format($transaction->total, 0, ',', ' ') }} FCFA
                                    </td>
                                    <td class="px-6 py-4">
                                        @if ($transaction->statut === 'en attente')
                                            <span class="bg-yellow-500 text-white px-3 py-1 rounded-full text-xs font-semibold">
                                                ⏳ En Attente
                                            </span>
                                        @elseif ($transaction->statut === 'effectuée')
                                            <span class="bg-green-500 text-white px-3 py-1 rounded-full text-xs font-semibold">
                                                ✅ Effectuée
                                            </span>
                                        @else
                                            <span class="bg-red-500 text-white px-3 py-1 rounded-full text-xs font-semibold">
                                                ❌ Annulée
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 flex justify-center space-x-2">
                                        @if ($transaction->statut === 'effectuée')
                                            <a href="{{ route('transactions.facture', $transaction) }}" class="bg-gray-700 hover:bg-gray-800 text-white px-4 py-2 rounded-md shadow-md transition duration-300">
                                                🧾 Facture
                                            </a>
                                            <a href="{{ route('transactions.edit', $transaction) }}" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md shadow-md transition duration-300">
                                                ✏️ Modifier
                                            </a>
                                            <form action="{{ route('transactions.destroy', $transaction) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-md shadow-md transition duration-300">
                                                    🗑️ Annuler
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-gray-400 text-sm">Aucune action</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                                        Aucune transaction trouvée.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                 <!--Version mobile-->
                <div class="sm:hidden space-y-4">
                    @forelse ($transactions as $transaction)
                        <div class="bg-white dark:bg-gray-700 p-4 rounded-lg shadow-md">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                {{ $transaction->produit->nom }}
                            </h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                Quantité: <span class="font-semibold">{{ $transaction->quantite }}</span>
                            </p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                Prix_Total: <span class="font-semibold text-blue-500">{{ number_format($transaction->total, 0, ',', ' ') }} FCFA</span>
                            </p>
                            <p class="text-sm">
                                @if ($transaction->statut === 'en attente')
                                    <span class="bg-yellow-500 text-white px-3 py-1 rounded-full text-xs font-semibold">
                                        ⏳ En Attente
                                    </span>
                                @elseif ($transaction->statut === 'effectuée')
                                    <span class="bg-green-500 text-white px-3 py-1 rounded-full text-xs font-semibold">
                                        ✅ Effectuée
                                    </span>
                                @else
                                    <span class="bg-red-500 text-white px-3 py-1 rounded-full text-xs font-semibold">
                                        ❌ Annulée
                                    </span>
                                @endif
                            </p>
                            <div class="flex flex-col mt-4 space-y-2">
                                @if ($transaction->statut === 'effectuée')
                                    <a href="{{ route('transactions.facture', $transaction) }}" class="bg-gray-700 hover:bg-gray-800 text-white px-4 py-2 rounded-md shadow-md text-center">
                                        🧾 Facture
                                    </a>
                                    <a href="{{ route('transactions.edit', $transaction) }}" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md shadow-md text-center">
                                        ✏️ Modifier
                                    </a>
                                    <form action="{{ route('transactions.destroy', $transaction) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-md shadow-md w-full">
                                            🗑️ Annuler
                                        </button>
                                    </form>
                                @else
                                    <span class="text-gray-400 text-sm text-center">Aucune action</span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <p class="text-center text-gray-500 dark:text-gray-400 py-8">
                            Aucune transaction trouvée.
                        </p>
                    @endforelse
                </div>

            </div>

            <!-- Pagination -->
            <div class="mt-6">
                {{ $transactions->links() }}
            </div>

        </div>
    </div>
</x-app-layout>

