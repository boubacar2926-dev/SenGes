<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('💵 Nouvelle Transaction') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div
            class="max-w-4xl mx-auto sm:px-6 lg:px-8 bg-white dark:bg-gray-800 shadow-lg rounded-lg p-6"
            x-data="transactionForm(@json($produits->map(fn ($p) => ['id' => $p->id, 'nom' => $p->nom, 'prix' => (float) $p->prix])->values()))"
        >
            <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300 mb-4">📌 Détails de la Transaction</h3>

            @if ($produits->isEmpty())
                <p class="text-gray-500 dark:text-gray-400">Ajoute d'abord un produit avant de créer une transaction.</p>
            @else
                <form action="{{ route('transactions.store') }}" method="POST" class="space-y-6">
                    @csrf

                    <div class="space-y-4">
                        <template x-for="(ligne, index) in lignes" :key="index">
                            <div class="flex flex-col sm:flex-row gap-3 sm:items-end border-b border-gray-200 dark:border-gray-700 pb-4">
                                <div class="flex-1">
                                    <label class="block text-gray-700 dark:text-gray-300 font-semibold mb-2">Produit</label>
                                    <select
                                        :name="`items[${index}][produit_id]`"
                                        x-model="ligne.produit_id"
                                        required
                                        class="w-full p-3 border border-gray-300 dark:border-gray-700 rounded-lg focus:ring focus:ring-blue-200 dark:bg-gray-700 dark:text-white"
                                    >
                                        <template x-for="produit in produits" :key="produit.id">
                                            <option :value="produit.id" x-text="`${produit.nom} (${formatFcfa(produit.prix)} FCFA)`"></option>
                                        </template>
                                    </select>
                                </div>

                                <div class="w-full sm:w-32">
                                    <label class="block text-gray-700 dark:text-gray-300 font-semibold mb-2">Quantité</label>
                                    <input
                                        type="number"
                                        min="1"
                                        :name="`items[${index}][quantite]`"
                                        x-model.number="ligne.quantite"
                                        required
                                        class="w-full p-3 border border-gray-300 dark:border-gray-700 rounded-lg focus:ring focus:ring-blue-200 dark:bg-gray-700 dark:text-white"
                                    >
                                </div>

                                <div class="w-full sm:w-36 text-right font-semibold text-gray-700 dark:text-gray-300">
                                    <span x-text="`${formatFcfa(sousTotal(ligne))} FCFA`"></span>
                                </div>

                                <button
                                    type="button"
                                    @click="removeLigne(index)"
                                    x-show="lignes.length > 1"
                                    class="text-red-500 hover:text-red-700 font-bold px-2 py-2"
                                    title="Retirer cette ligne"
                                >
                                    ✕
                                </button>
                            </div>
                        </template>
                    </div>

                    <button
                        type="button"
                        @click="addLigne()"
                        class="text-blue-600 hover:text-blue-800 font-semibold"
                    >
                        + Ajouter un produit
                    </button>

                    <!-- Prix total -->
                    <div class="text-center bg-gray-100 dark:bg-gray-700 p-4 rounded-lg shadow-md">
                        <p class="text-lg font-semibold text-gray-800 dark:text-gray-200">
                            🏷️ Prix Total : <span x-text="formatFcfa(total())" class="text-blue-500"></span> FCFA
                        </p>
                    </div>

                    <!-- Bouton d'enregistrement -->
                    <div class="flex justify-center">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-3 rounded-lg shadow-md transition duration-300">
                            💾 Enregistrer la Transaction
                        </button>
                    </div>
                </form>
            @endif
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('transactionForm', (produits) => ({
                produits,
                lignes: [{ produit_id: produits[0]?.id ?? '', quantite: 1 }],

                addLigne() {
                    this.lignes.push({ produit_id: this.produits[0]?.id ?? '', quantite: 1 });
                },

                removeLigne(index) {
                    if (this.lignes.length > 1) {
                        this.lignes.splice(index, 1);
                    }
                },

                prixDe(produitId) {
                    const produit = this.produits.find((p) => p.id == produitId);
                    return produit ? produit.prix : 0;
                },

                sousTotal(ligne) {
                    return this.prixDe(ligne.produit_id) * (ligne.quantite || 0);
                },

                total() {
                    return this.lignes.reduce((sum, ligne) => sum + this.sousTotal(ligne), 0);
                },

                formatFcfa(valeur) {
                    return new Intl.NumberFormat('fr-FR').format(Math.round(valeur || 0));
                },
            }));
        });
    </script>
</x-app-layout>
