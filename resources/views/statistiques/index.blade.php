<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('📊 Statistiques des Ventes') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <!-- Cartes Résumé des ventes -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-2 gap-6">
                <div class="bg-white dark:bg-gray-800 shadow-lg rounded-lg p-6 text-center hover:shadow-xl transition duration-300">
                    <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300">💰 Total des ventes</h3>
                    <p class="text-2xl font-bold text-blue-500 mt-2">{{ number_format($totalVentes, 0, ',', ' ') }} FCFA</p>
                </div>

                <div class="bg-white dark:bg-gray-800 shadow-lg rounded-lg p-6 text-center hover:shadow-xl transition duration-300">
                    <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300">🛒 Nombre total de transactions</h3>
                    <p class="text-2xl font-bold text-green-500 mt-2">{{ $nombreTransactions }}</p>
                </div>
            </div>

            <!-- Tableau des Produits les plus vendus -->
            <div class="bg-white dark:bg-gray-800 shadow-lg rounded-lg mt-6 p-6">
                <h3 class="text-lg font-bold text-gray-700 dark:text-gray-300 mb-4">🔥 Produits les Plus Vendus</h3>

                <div class="overflow-x-auto">
                    <table class="w-full border-collapse">
                        <thead class="bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-200 uppercase text-sm font-semibold">
                            <tr class="text-left">
                                <th class="px-6 py-3">Produit</th>
                                <th class="px-6 py-3 text-center">Quantité Vendue</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-600">
                            @foreach ($produitsPopulaires as $produit)
                                <tr class="hover:bg-gray-100 dark:hover:bg-gray-700 transition duration-200">
                                    <td class="px-6 py-4 text-gray-800 dark:text-gray-200">{{ $produit->produit->nom }}</td>
                                    <td class="px-6 py-4 text-center font-semibold text-blue-500">{{ $produit->total_quantite }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
