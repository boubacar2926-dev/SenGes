<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('🛍️ Mes Produits') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">




            <!-- Bouton Ajouter un Produit -->
            <div class="flex justify-between items-center mb-6">
                <a href="{{ route('produits.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-2 rounded-lg shadow-md transition duration-300">
                    + Ajouter un Produit
                </a>
            </div>

            <!-- Barre de recherche -->
            <div
                x-data="searchSuggestions('{{ route('produits.suggestions') }}', {{ \Illuminate\Support\Js::from($search ?? '') }})"
                @click.outside="open = false"
                class="relative mb-6"
            >
                <form method="GET" action="{{ route('produits.index') }}" x-ref="form" class="flex gap-2">
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
                            placeholder="Rechercher un produit par nom..."
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
                        <a href="{{ route('produits.index', array_filter(['sort' => $sort, 'direction' => $direction])) }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-md shadow-md transition duration-300">
                            Effacer
                        </a>
                    @endif
                </form>
            </div>

            <!-- Message de succès -->
            @if(session('success'))
                <div class="bg-green-500 text-white p-3 rounded-lg shadow-md mb-4">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Alerte stock faible -->
            @if($produitsStockFaible->isNotEmpty())
                <div class="bg-yellow-100 dark:bg-yellow-900 border border-yellow-400 dark:border-yellow-700 text-yellow-800 dark:text-yellow-200 p-4 rounded-lg shadow-md mb-4">
                    ⚠️ Stock faible (moins de {{ \App\Http\Controllers\ProduitController::SEUIL_STOCK_FAIBLE }}) pour :
                    <strong>{{ $produitsStockFaible->implode(', ') }}</strong>
                </div>
            @endif

            @php
                $sortLink = function (string $column) use ($sort, $direction, $search) {
                    $newDirection = ($sort === $column && $direction === 'asc') ? 'desc' : 'asc';
                    return route('produits.index', array_filter([
                        'search' => $search,
                        'sort' => $column,
                        'direction' => $newDirection,
                    ]));
                };
                $sortIndicator = fn (string $column) => $sort === $column ? ($direction === 'asc' ? '▲' : '▼') : '';
            @endphp

            <!-- Version Desktop : Tableau -->
            <div class="hidden sm:block bg-white dark:bg-gray-800 shadow-lg rounded-lg overflow-hidden">
                <table class="w-full border-collapse">
                    <thead class="bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-200 uppercase text-sm font-semibold">
                        <tr>
                            <th class="px-6 py-3 text-left">
                                <a href="{{ $sortLink('nom') }}" class="hover:underline">Nom {{ $sortIndicator('nom') }}</a>
                            </th>
                            <th class="px-6 py-3 text-left">Description</th>
                            <th class="px-6 py-3 text-left">
                                <a href="{{ $sortLink('prix') }}" class="hover:underline">Prix {{ $sortIndicator('prix') }}</a>
                            </th>
                            <th class="px-6 py-3 text-center">
                                <a href="{{ $sortLink('quantite') }}" class="hover:underline">Quantité {{ $sortIndicator('quantite') }}</a>
                            </th>
                            <th class="px-6 py-3 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-600">
                        @forelse ($produits as $produit)
                            <tr class="hover:bg-gray-100 dark:hover:bg-gray-700 transition duration-200">
                                <td class="px-6 py-4 font-medium text-gray-800 dark:text-gray-300">{{ $produit->nom }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">{{ $produit->description }}</td>
                                <td class="px-6 py-4 font-semibold text-blue-500">{{ number_format($produit->prix, 0, ',', ' ') }} FCFA</td>
                                <td class="px-6 py-4 text-center">
                                    {{ $produit->quantite }}
                                    @if($produit->quantite < \App\Http\Controllers\ProduitController::SEUIL_STOCK_FAIBLE)
                                        <span class="ml-1 bg-yellow-200 dark:bg-yellow-800 text-yellow-800 dark:text-yellow-200 text-xs font-semibold px-2 py-1 rounded-full">⚠️ Faible</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 flex justify-center space-x-2">
                                    <a href="{{ route('produits.reapprovisionnements.index', $produit) }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md shadow-md transition duration-300">
                                        📦 Stock
                                    </a>
                                    <a href="{{ route('produits.edit', $produit) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md shadow-md transition duration-300">
                                        ✏️ Modifier
                                    </a>
                                    <form action="{{ route('produits.destroy', $produit) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-md shadow-md transition duration-300">
                                            🗑️ Supprimer
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                                    Aucun produit trouvé.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Version Mobile : Affichage sous forme de cartes -->
            <div class="sm:hidden space-y-4">
                @forelse ($produits as $produit)
                    <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-md">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                            {{ $produit->nom }}
                        </h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            {{ $produit->description }}
                        </p>
                        <p class="text-sm font-semibold text-blue-500">
                            Prix: {{ number_format($produit->prix, 0, ',', ' ') }} FCFA
                        </p>
                        <p class="text-sm">
                            Quantité: <span class="font-semibold">{{ $produit->quantite }}</span>
                            @if($produit->quantite < \App\Http\Controllers\ProduitController::SEUIL_STOCK_FAIBLE)
                                <span class="ml-1 bg-yellow-200 dark:bg-yellow-800 text-yellow-800 dark:text-yellow-200 text-xs font-semibold px-2 py-1 rounded-full">⚠️ Faible</span>
                            @endif
                        </p>
                        <div class="flex flex-col mt-4 space-y-2">
                            <a href="{{ route('produits.reapprovisionnements.index', $produit) }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md shadow-md text-center">
                                📦 Stock
                            </a>
                            <a href="{{ route('produits.edit', $produit) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md shadow-md text-center">
                                ✏️ Modifier
                            </a>
                            <form action="{{ route('produits.destroy', $produit) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-md shadow-md w-full">
                                    🗑️ Supprimer
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <p class="text-center text-gray-500 dark:text-gray-400 py-8">
                        Aucun produit trouvé.
                    </p>
                @endforelse
            </div>

            <!-- Pagination -->
            <div class="mt-6">
                {{ $produits->links() }}
            </div>

        </div>
    </div>
</x-app-layout>
