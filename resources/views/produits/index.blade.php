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

            <!-- Message de succès -->
            @if(session('success'))
                <div class="bg-green-500 text-white p-3 rounded-lg shadow-md mb-4">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Version Desktop : Tableau -->
            <div class="hidden sm:block bg-white dark:bg-gray-800 shadow-lg rounded-lg overflow-hidden">
                <table class="w-full border-collapse">
                    <thead class="bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-200 uppercase text-sm font-semibold">
                        <tr>
                            <th class="px-6 py-3 text-left">Nom</th>
                            <th class="px-6 py-3 text-left">Description</th>
                            <th class="px-6 py-3 text-left">Prix</th>
                            <th class="px-6 py-3 text-center">Quantité</th>
                            <th class="px-6 py-3 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-600">
                        @foreach ($produits as $produit)
                            <tr class="hover:bg-gray-100 dark:hover:bg-gray-700 transition duration-200">
                                <td class="px-6 py-4 font-medium text-gray-800 dark:text-gray-300">{{ $produit->nom }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">{{ $produit->description }}</td>
                                <td class="px-6 py-4 font-semibold text-blue-500">{{ number_format($produit->prix, 0, ',', ' ') }} FCFA</td>
                                <td class="px-6 py-4 text-center">{{ $produit->quantite }}</td>
                                <td class="px-6 py-4 flex justify-center space-x-2">
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
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Version Mobile : Affichage sous forme de cartes -->
            <div class="sm:hidden space-y-4">
                @foreach ($produits as $produit)
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
                        </p>
                        <div class="flex flex-col mt-4 space-y-2">
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
                @endforeach
            </div>

        </div>
    </div>
</x-app-layout>
