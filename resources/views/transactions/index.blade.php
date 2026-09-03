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
                        <a href="{{ route('transactions.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-md shadow-md transition duration-300">
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
            @if(session('error'))
                <div class="bg-red-500 text-white p-3 rounded-lg shadow-md mb-4">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Une carte par vente : les lignes créées ensemble occupent le même bloc -->
            <div class="space-y-4">
                @forelse ($groupedTransactions as $groupeId => $lignes)
                    @php
                        $premiereLigne = $lignes->first();
                        $auMoinsUneEffectuee = $lignes->contains('statut', 'effectuée');
                    @endphp
                    <div class="bg-white dark:bg-gray-800 shadow-lg rounded-lg overflow-hidden">
                        <div class="flex flex-wrap items-center justify-between gap-2 px-6 py-3 bg-gray-100 dark:bg-gray-700">
                            <div class="text-sm text-gray-600 dark:text-gray-300">
                                @if ($premiereLigne->numero_facture)
                                    <span class="font-semibold">Facture #{{ str_pad($premiereLigne->numero_facture, 6, '0', STR_PAD_LEFT) }}</span>
                                    ·
                                @endif
                                {{ $premiereLigne->created_at->format('d/m/Y H:i') }}
                                · {{ $lignes->count() }} {{ \Illuminate\Support\Str::plural('produit', $lignes->count()) }}
                                · <span class="font-semibold text-blue-500">{{ number_format($lignes->sum('total'), 0, ',', ' ') }} FCFA</span>
                            </div>
                            @if ($auMoinsUneEffectuee)
                                <a href="{{ route('transactions.facture', $groupeId) }}" class="bg-gray-700 hover:bg-gray-800 text-white px-4 py-2 rounded-md shadow-md text-sm transition duration-300">
                                    🧾 Facture
                                </a>
                            @endif
                        </div>

                        <div class="divide-y divide-gray-200 dark:divide-gray-600">
                            @foreach ($lignes as $transaction)
                                <div class="flex flex-wrap items-center gap-3 px-6 py-3">
                                    <div class="flex-1 min-w-[12rem]">
                                        <p class="font-medium text-gray-800 dark:text-gray-200">{{ $transaction->produit->nom }}</p>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ $transaction->quantite }} × {{ number_format($transaction->produit->prix, 0, ',', ' ') }} FCFA
                                            = <span class="font-semibold text-blue-500">{{ number_format($transaction->total, 0, ',', ' ') }} FCFA</span>
                                        </p>
                                    </div>

                                    <div>
                                        @if ($transaction->statut === 'en attente')
                                            <span class="bg-yellow-500 text-white px-3 py-1 rounded-full text-xs font-semibold">⏳ En Attente</span>
                                        @elseif ($transaction->statut === 'effectuée')
                                            <span class="bg-green-500 text-white px-3 py-1 rounded-full text-xs font-semibold">✅ Effectuée</span>
                                        @else
                                            <span class="bg-red-500 text-white px-3 py-1 rounded-full text-xs font-semibold">❌ Annulée</span>
                                        @endif
                                    </div>

                                    <div class="flex gap-2">
                                        @if ($transaction->statut === 'effectuée')
                                            <a href="{{ route('transactions.edit', $transaction) }}" class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-2 rounded-md shadow-md text-sm transition duration-300">
                                                ✏️ Modifier
                                            </a>
                                            <form action="{{ route('transactions.destroy', $transaction) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-3 py-2 rounded-md shadow-md text-sm transition duration-300">
                                                    🗑️ Annuler
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-gray-400 text-sm">Aucune action</span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @empty
                    <div class="bg-white dark:bg-gray-800 shadow-lg rounded-lg p-8 text-center text-gray-500 dark:text-gray-400">
                        Aucune transaction trouvée.
                    </div>
                @endforelse
            </div>

            <!-- Pagination -->
            <div class="mt-6">
                {{ $transactions->links() }}
            </div>

        </div>
    </div>
</x-app-layout>
