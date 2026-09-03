<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('📦 Réapprovisionnement — ') }}{{ $produit->nom }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div class="bg-green-500 text-white p-3 rounded-lg shadow-md">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 shadow-lg rounded-lg p-6">
                <p class="text-gray-600 dark:text-gray-400 mb-4">
                    Stock actuel : <span class="font-semibold text-gray-900 dark:text-gray-100">{{ $produit->quantite }}</span>
                </p>

                <form method="POST" action="{{ route('produits.reapprovisionnements.store', $produit) }}" class="flex items-end gap-3">
                    @csrf
                    <div class="flex-1">
                        <label for="quantite" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Quantité reçue</label>
                        <input type="number" name="quantite" id="quantite" min="1" required value="{{ old('quantite') }}"
                            class="mt-1 w-full rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        @error('quantite')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-2 rounded-lg shadow-md transition duration-300">
                        + Ajouter au stock
                    </button>
                </form>
            </div>

            <div class="bg-white dark:bg-gray-800 shadow-lg rounded-lg overflow-hidden">
                <h3 class="px-6 py-4 font-semibold text-gray-800 dark:text-gray-200 border-b border-gray-200 dark:border-gray-700">
                    Historique
                </h3>

                @if ($reapprovisionnements->isEmpty())
                    <p class="p-6 text-gray-500 dark:text-gray-400">Aucun réapprovisionnement enregistré pour ce produit.</p>
                @else
                    <table class="w-full border-collapse">
                        <thead class="bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-200 uppercase text-sm font-semibold">
                            <tr>
                                <th class="px-6 py-3 text-left">Date</th>
                                <th class="px-6 py-3 text-left">Ajouté par</th>
                                <th class="px-6 py-3 text-right">Quantité</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-600">
                            @foreach ($reapprovisionnements as $reappro)
                                <tr>
                                    <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400 whitespace-nowrap">
                                        {{ $reappro->created_at->format('d/m/Y H:i') }}
                                    </td>
                                    <td class="px-6 py-4 text-gray-800 dark:text-gray-300">{{ $reappro->user->name }}</td>
                                    <td class="px-6 py-4 text-right font-semibold text-green-600">+{{ $reappro->quantite }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>

            <a href="{{ route('produits.index') }}" class="inline-block text-blue-600 hover:underline">← Retour aux produits</a>

        </div>
    </div>
</x-app-layout>
