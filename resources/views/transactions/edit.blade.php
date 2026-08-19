<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('✏️ Modifier la Transaction') }}
        </h2>
    </x-slot>

    <div class="py-6 sm:py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 shadow-lg rounded-lg p-4 sm:p-6">
                <form action="{{ route('transactions.update', $transaction) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <!-- Sélection du produit -->
                    <div class="mb-4">
                        <label for="produit_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Produit
                        </label>
                        <select name="produit_id" id="produit_id"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200 sm:text-sm"
                                required>
                            @foreach ($produits as $produit)
                                <option value="{{ $produit->id }}" {{ $transaction->produit_id === $produit->id ? 'selected' : '' }}>
                                    {{ $produit->nom }} (Stock: {{ $produit->quantite }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Champ Quantité -->
                    <div class="mb-4">
                        <label for="quantite" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Quantité
                        </label>
                        <input type="number" name="quantite" id="quantite" value="{{ $transaction->quantite }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200 sm:text-sm"
                               required>
                    </div>

                    <!-- Champ Statut -->
                    <div class="mb-4">
                        <label for="statut" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Statut
                        </label>
                        <select name="statut" id="statut"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200 sm:text-sm"
                                required>
                            <option value="en attente" {{ $transaction->statut === 'en attente' ? 'selected' : '' }}>⏳ En Attente</option>
                            <option value="effectuée" {{ $transaction->statut === 'effectuée' ? 'selected' : '' }}>✅ Effectuée</option>
                            <option value="annulée" {{ $transaction->statut === 'annulée' ? 'selected' : '' }}>❌ Annulée</option>
                        </select>
                    </div>

                    <!-- Bouton de soumission -->
                    <div class="flex justify-end">
                        <button type="submit"
                                class="bg-blue-500 hover:bg-blue-600 text-white font-semibold px-4 py-2 rounded-lg shadow-md transition duration-300 text-sm sm:text-base sm:px-6 sm:py-2">
                            Enregistrer les modifications
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
