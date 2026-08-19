<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Tableau de Bord Administrateur') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <!-- Cartes Résumé des Statistiques -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Carte Nombre de Commerçants -->
                <div class="bg-white dark:bg-gray-800 shadow-lg rounded-lg p-6 text-center hover:shadow-xl transition duration-300">
                    <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300">👥 Commerçants</h3>
                    <p class="text-2xl font-bold text-blue-500 mt-2">{{ $totalCommercants }}</p>
                </div>

                <!-- Carte Nombre de Produits -->
                <div class="bg-white dark:bg-gray-800 shadow-lg rounded-lg p-6 text-center hover:shadow-xl transition duration-300">
                    <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300">🛍️ Produits</h3>
                    <p class="text-2xl font-bold text-green-500 mt-2">{{ $totalProduits }}</p>
                </div>

                <!-- Carte Nombre de Transactions -->
                <div class="bg-white dark:bg-gray-800 shadow-lg rounded-lg p-6 text-center hover:shadow-xl transition duration-300">
                    <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300">💳 Transactions</h3>
                    <p class="text-2xl font-bold text-purple-500 mt-2">{{ $totalTransactions }}</p>
                </div>

                <!-- Carte Revenu Total -->
                <div class="bg-white dark:bg-gray-800 shadow-lg rounded-lg p-6 text-center hover:shadow-xl transition duration-300">
                    <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300">💰 Revenu Total</h3>
                    <p class="text-2xl font-bold text-orange-500 mt-2">{{ number_format($revenuTotal, 2) }} FCFA</p>
                </div>
            </div>

            <!-- Section Statistiques Globales -->
            <div class="bg-white dark:bg-gray-800 shadow-lg rounded-lg mt-6 p-6">
                <h3 class="text-lg font-bold text-gray-700 dark:text-gray-300 mb-4">📊 Statistiques Globales</h3>
                <div class="space-y-4">
                    <p><strong>Nombre de Commerçants :</strong> {{ $totalCommercants }}</p>
                    <p><strong>Nombre de Produits :</strong> {{ $totalProduits }}</p>
                    <p><strong>Nombre de Transactions :</strong> {{ $totalTransactions }}</p>
                    <p><strong>Revenu Total :</strong> {{ number_format($revenuTotal, 2) }} FCFA</p>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
