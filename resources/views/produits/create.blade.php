<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('➕ Ajouter un Produit') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 shadow-lg rounded-lg p-6">

                <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300 mb-4">🛍️ Détails du Produit</h3>

                <!-- Formulaire d'ajout de produit -->
                <form action="{{ route('produits.store') }}" method="POST" class="space-y-6">
                    @csrf

                    <!-- Nom du Produit -->
                    <div>
                        <label for="nom" class="block text-gray-700 dark:text-gray-300 font-semibold mb-2">
                            Nom du produit :
                        </label>
                        <input type="text" name="nom" id="nom" required
                            class="w-full p-3 border border-gray-300 dark:border-gray-700 rounded-lg focus:ring focus:ring-blue-200 dark:bg-gray-700 dark:text-white">
                    </div>

                    <!-- Description -->
                    <div>
                        <label for="description" class="block text-gray-700 dark:text-gray-300 font-semibold mb-2">
                            Description :
                        </label>
                        <textarea name="description" id="description" rows="3"
                            class="w-full p-3 border border-gray-300 dark:border-gray-700 rounded-lg focus:ring focus:ring-blue-200 dark:bg-gray-700 dark:text-white"></textarea>
                    </div>

                    <!-- Prix -->
                    <div>
                        <label for="prix" class="block text-gray-700 dark:text-gray-300 font-semibold mb-2">
                            Prix (FCFA) :
                        </label>
                        <input type="number" name="prix" id="prix" step="0.01" required
                            class="w-full p-3 border border-gray-300 dark:border-gray-700 rounded-lg focus:ring focus:ring-blue-200 dark:bg-gray-700 dark:text-white">
                    </div>

                    <!-- Quantité -->
                    <div>
                        <label for="quantite" class="block text-gray-700 dark:text-gray-300 font-semibold mb-2">
                            Quantité :
                        </label>
                        <input type="number" name="quantite" id="quantite" required
                            class="w-full p-3 border border-gray-300 dark:border-gray-700 rounded-lg focus:ring focus:ring-blue-200 dark:bg-gray-700 dark:text-white">
                    </div>

                    <!-- Bouton d'ajout -->
                    <div class="flex justify-center">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-3 rounded-lg shadow-md transition duration-300">
                            ➕ Ajouter le Produit
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>
