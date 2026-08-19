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

         <!--Message de succès-->
            @if(session('success'))
                <div class="bg-green-500 text-white p-3 rounded-lg shadow-md mb-4">
                    {{ session('success') }}
                </div>
            @endif

           <!-- Tableau des transactions-->
            <div class="bg-white dark:bg-gray-800 shadow-lg rounded-lg overflow-hidden">
                <div class="hidden sm:block">
                    <table class="w-full border-collapse">
                        <thead class="bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-200 uppercase text-sm font-semibold">
                            <tr class="text-center">
                                <th class="px-6 py-3">Produit</th>
                                <th class="px-6 py-3">Quantité</th>
                                <th class="px-6 py-3">Prix_Total</th>
                                <th class="px-6 py-3">Statut</th>
                                <th class="px-6 py-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-600">
                            @foreach ($transactions as $transaction)
                                <tr class="hover:bg-gray-100 dark:hover:bg-gray-700 transition duration-200 text-center">
                                    <td class="px-6 py-4 font-medium text-gray-800 dark:text-gray-300">
                                        {{ $transaction->produit->nom }}
                                    </td>
                                    <td class="px-6 py-4">{{ $transaction->quantite }}</td>
                                    <td class="px-6 py-4 font-semibold text-blue-500">
                                        {{ number_format($transaction->total, 0, ',', ' ') }} FCFA
                                    </td>
                                    <td class="px-6 py-4">
                                        @if ($transaction->statut === 'en attente')
                                            <span class="bg-yellow-500 text-white px-3 py-1 rounded-full text-xs font-semibold">
                                                ⏳ En Attente
                                            </span>
                                        @elseif ($transaction->statut === 'effectuée')
                                            <span class="bg-green-500 text-white px-3 py-1 rounded-full text-xs font-semibold">
                                                ✅ Effectuée
                                            </span>
                                        @else
                                            <span class="bg-red-500 text-white px-3 py-1 rounded-full text-xs font-semibold">
                                                ❌ Annulée
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 flex justify-center space-x-2">
                                        @if ($transaction->statut === 'effectuée')
                                            <a href="{{ route('transactions.edit', $transaction) }}" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md shadow-md transition duration-300">
                                                ✏️ Modifier
                                            </a>
                                            <form action="{{ route('transactions.destroy', $transaction) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-md shadow-md transition duration-300">
                                                    🗑️ Annuler
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-gray-400 text-sm">Aucune action</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                 <!--Version mobile-->
                <div class="sm:hidden space-y-4">
                    @foreach ($transactions as $transaction)
                        <div class="bg-white dark:bg-gray-700 p-4 rounded-lg shadow-md">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                {{ $transaction->produit->nom }}
                            </h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                Quantité: <span class="font-semibold">{{ $transaction->quantite }}</span>
                            </p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                Prix_Total: <span class="font-semibold text-blue-500">{{ number_format($transaction->total, 0, ',', ' ') }} FCFA</span>
                            </p>
                            <p class="text-sm">
                                @if ($transaction->statut === 'en attente')
                                    <span class="bg-yellow-500 text-white px-3 py-1 rounded-full text-xs font-semibold">
                                        ⏳ En Attente
                                    </span>
                                @elseif ($transaction->statut === 'effectuée')
                                    <span class="bg-green-500 text-white px-3 py-1 rounded-full text-xs font-semibold">
                                        ✅ Effectuée
                                    </span>
                                @else
                                    <span class="bg-red-500 text-white px-3 py-1 rounded-full text-xs font-semibold">
                                        ❌ Annulée
                                    </span>
                                @endif
                            </p>
                            <div class="flex flex-col mt-4 space-y-2">
                                @if ($transaction->statut === 'effectuée')
                                    <a href="{{ route('transactions.edit', $transaction) }}" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md shadow-md text-center">
                                        ✏️ Modifier
                                    </a>
                                    <form action="{{ route('transactions.destroy', $transaction) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-md shadow-md w-full">
                                            🗑️ Annuler
                                        </button>
                                    </form>
                                @else
                                    <span class="text-gray-400 text-sm text-center">Aucune action</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

            </div>

        </div>
    </div>
</x-app-layout>

