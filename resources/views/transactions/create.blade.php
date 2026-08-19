<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('💵 Nouvelle Transaction') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 bg-white dark:bg-gray-800 shadow-lg rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300 mb-4">📌 Détails de la Transaction</h3>

            <!-- Formulaire de création de transaction -->
            <form action="{{ route('transactions.store') }}" method="POST" class="space-y-6">
                @csrf

                <!-- Sélection du produit -->
                <div>
                    <label for="produit_id" class="block text-gray-700 dark:text-gray-300 font-semibold mb-2">
                        Produit :
                    </label>
                    <select name="produit_id" id="produit_id" required
                        class="w-full p-3 border border-gray-300 dark:border-gray-700 rounded-lg focus:ring focus:ring-blue-200 dark:bg-gray-700 dark:text-white"
                        onchange="updateTotal()">
                        @foreach ($produits as $produit)
                            <option value="{{ $produit->id }}" data-prix="{{ $produit->prix }}">
                                {{ $produit->nom }} ({{ number_format($produit->prix, 0, ',', ' ') }} FCFA)
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Quantité -->
                <div>
                    <label for="quantite" class="block text-gray-700 dark:text-gray-300 font-semibold mb-2">
                        Quantité :
                    </label>
                    <input type="number" name="quantite" id="quantite" min="1" required
                        class="w-full p-3 border border-gray-300 dark:border-gray-700 rounded-lg focus:ring focus:ring-blue-200 dark:bg-gray-700 dark:text-white"
                        oninput="updateTotal()">
                </div>

                <!-- Prix total -->
                <div class="text-center bg-gray-100 dark:bg-gray-700 p-4 rounded-lg shadow-md">
                    <p class="text-lg font-semibold text-gray-800 dark:text-gray-200">
                        🏷️ Prix Total : <span id="totalPrix" class="text-blue-500">0</span> FCFA
                    </p>
                </div>

                <!-- Bouton d'enregistrement -->
                <div class="flex justify-center">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-3 rounded-lg shadow-md transition duration-300">
                        💾 Enregistrer la Transaction
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Script pour calculer le prix total en fonction de la quantité -->
    <script>
        function updateTotal() {
            const produitSelect = document.getElementById('produit_id');
            const selectedProduit = produitSelect.options[produitSelect.selectedIndex];
            const prixUnitaire = selectedProduit.dataset.prix;
            const quantite = document.getElementById('quantite').value || 0;
            const total = prixUnitaire * quantite;

            document.getElementById('totalPrix').textContent = new Intl.NumberFormat('fr-FR').format(total);
        }
    </script>

</x-app-layout>
