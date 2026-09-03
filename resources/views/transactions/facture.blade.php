<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @php($numeroAffiche = $transactions->first()->numero_facture ?? $transactions->min('id'))
    <title>Facture #{{ str_pad($numeroAffiche, 6, '0', STR_PAD_LEFT) }} — {{ config('app.name') }}</title>
    @vite(['resources/css/app.css'])
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; }
        }
    </style>
</head>
<body class="bg-gray-100 dark:bg-gray-100 text-gray-900">
    <div class="max-w-3xl mx-auto py-10 px-6">

        <div class="no-print flex justify-between items-center mb-6">
            <a href="{{ route('transactions.index') }}" class="text-blue-600 hover:underline">&larr; Retour aux transactions</a>
            <button onclick="window.print()" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-2 rounded-lg shadow-md transition duration-300">
                🖨️ Imprimer
            </button>
        </div>

        <div class="bg-white shadow-lg rounded-lg p-8">
            <div class="flex justify-between items-start border-b pb-6 mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">{{ $transactions->first()->user->name }}</h1>
                    <p class="text-sm text-gray-500">{{ $transactions->first()->user->email }}</p>
                </div>
                <div class="text-right">
                    <h2 class="text-xl font-semibold text-gray-800">FACTURE</h2>
                    <p class="text-sm text-gray-500">N° {{ str_pad($numeroAffiche, 6, '0', STR_PAD_LEFT) }}</p>
                    <p class="text-sm text-gray-500">{{ $transactions->first()->created_at->format('d/m/Y') }}</p>
                </div>
            </div>

            <table class="w-full border-collapse mb-6">
                <thead>
                    <tr class="bg-gray-100 text-gray-700 text-sm uppercase">
                        <th class="text-left px-4 py-2">Produit</th>
                        <th class="text-right px-4 py-2">Prix unitaire</th>
                        <th class="text-right px-4 py-2">Quantité</th>
                        <th class="text-right px-4 py-2">Total</th>
                        <th class="text-right px-4 py-2">Statut</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($transactions as $transaction)
                        <tr class="border-b">
                            <td class="px-4 py-3">{{ $transaction->produit->nom }}</td>
                            <td class="px-4 py-3 text-right">{{ number_format($transaction->produit->prix, 0, ',', ' ') }} FCFA</td>
                            <td class="px-4 py-3 text-right">{{ $transaction->quantite }}</td>
                            <td class="px-4 py-3 text-right font-semibold">{{ number_format($transaction->total, 0, ',', ' ') }} FCFA</td>
                            <td class="px-4 py-3 text-right text-sm">
                                @if ($transaction->statut === 'effectuée') Effectuée
                                @elseif ($transaction->statut === 'en attente') En attente
                                @else Annulée
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" class="px-4 py-3 text-right font-semibold">Total</td>
                        <td colspan="2" class="px-4 py-3 text-right font-bold text-lg">{{ number_format($transactions->sum('total'), 0, ',', ' ') }} FCFA</td>
                    </tr>
                </tfoot>
            </table>
        </div>

    </div>
</body>
</html>
