<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('✏️ Modifier un Produit') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 shadow-lg rounded-lg p-6">

                <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300 mb-4">🔄 Modifier les Informations du Produit</h3>

                <!-- Formulaire de modification -->
                <form action="{{ route('produits.update', $produit->id) }}" method="POST" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <!-- Nom du Produit -->
                    <div>
                        <label for="nom" class="block text-gray-700 dark:text-gray-300 font-semibold mb-2">
                            Nom du produit :
                        </label>
                        <input type="text" name="nom" id="nom" value="{{ $produit->nom }}" required
                            class="w-full p-3 border border-gray-300 dark:border-gray-700 rounded-lg focus:ring focus:ring-green-200 dark:bg-gray-700 dark:text-white">
                    </div>

                    <!-- Description -->
                    <div>
                        <label for="description" class="block text-gray-700 dark:text-gray-300 font-semibold mb-2">
                            Description :
                        </label>
                        <textarea name="description" id="description" rows="3"
                            class="w-full p-3 border border-gray-300 dark:border-gray-700 rounded-lg focus:ring focus:ring-green-200 dark:bg-gray-700 dark:text-white">{{ $produit->description }}</textarea>
                    </div>

                    <!-- Prix -->
                    <div>
                        <label for="prix" class="block text-gray-700 dark:text-gray-300 font-semibold mb-2">
                            Prix (FCFA) :
                        </label>
                        <input type="number" name="prix" id="prix" step="0.01" value="{{ $produit->prix }}" required
                            class="w-full p-3 border border-gray-300 dark:border-gray-700 rounded-lg focus:ring focus:ring-green-200 dark:bg-gray-700 dark:text-white">
                    </div>

                    <!-- Quantité -->
                    <div>
                        <label for="quantite" class="block text-gray-700 dark:text-gray-300 font-semibold mb-2">
                            Quantité :
                        </label>
                        <input type="number" name="quantite" id="quantite" value="{{ $produit->quantite }}" required
                            class="w-full p-3 border border-gray-300 dark:border-gray-700 rounded-lg focus:ring focus:ring-green-200 dark:bg-gray-700 dark:text-white">
                    </div>

                    <!-- Bouton Mettre à Jour -->
                    <div class="flex justify-center">
                        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-semibold px-6 py-3 rounded-lg shadow-md transition duration-300">
                            ✅ Mettre à Jour
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>
