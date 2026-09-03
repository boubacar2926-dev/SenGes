<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTransactionRequest;
use App\Http\Requests\UpdateTransactionRequest;
use App\Models\Transaction;
use App\Models\Produit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TransactionController extends Controller
{
    // Afficher la liste des transactions, groupées par vente (lignes créées
    // ensemble dans la même soumission du formulaire)
    public function index(Request $request)
    {
        $search = $request->input('search');

        $matchingProduitIds = Produit::searchIdsForUser(Auth::id(), $search);

        $transactions = Transaction::where('user_id', Auth::id())
            ->when($matchingProduitIds !== null, fn ($query) => $query->whereIn('produit_id', $matchingProduitIds))
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(10)
            ->withQueryString();

        // Le tri par created_at place les lignes d'une même vente les unes à
        // la suite des autres : groupBy() les rassemble donc naturellement
        // dans l'ordre d'affichage, une vente = un bloc.
        $groupedTransactions = $transactions->getCollection()->groupBy('groupe_id');

        return view('transactions.index', [
            'transactions' => $transactions,
            'groupedTransactions' => $groupedTransactions,
            'search' => $search,
        ]);
    }

    // Afficher la facture imprimable d'un lot de transactions (une ou plusieurs,
    // créées ensemble dans la même soumission du formulaire)
    public function facture(string $groupeId)
    {
        $transactions = Transaction::where('groupe_id', $groupeId)
            ->with('produit')
            ->orderBy('id')
            ->get();

        abort_if($transactions->isEmpty(), 404);
        abort_unless($transactions->first()->user_id === Auth::id(), 403);

        return view('transactions.facture', compact('transactions'));
    }

    // Afficher le formulaire de création d'une transaction
    public function create()
    {
        $produits = Produit::where('user_id', Auth::id())->get();
        return view('transactions.create', compact('produits'));
    }

    // Enregistrer une ou plusieurs transactions (une par produit sélectionné)
    public function store(StoreTransactionRequest $request)
    {
        $items = $request->validated()['items'];
        $groupeId = (string) Str::uuid();

        try {
            DB::transaction(function () use ($items, $groupeId) {
                foreach ($items as $item) {
                    // Verrouille la ligne pour éviter qu'une requête concurrente
                    // ne vende le même stock en même temps. Si un même produit
                    // apparaît sur plusieurs lignes, chaque itération relit son
                    // stock déjà décrémenté par les lignes précédentes.
                    // Re-scope explicitement par utilisateur en plus de la validation
                    // (défense en profondeur contre une IDOR sur produit_id).
                    $produit = Produit::where('id', $item['produit_id'])
                        ->where('user_id', Auth::id())
                        ->lockForUpdate()
                        ->firstOrFail();

                    if ($produit->quantite < $item['quantite']) {
                        throw new \RuntimeException("Stock insuffisant pour « {$produit->nom} ».");
                    }

                    Transaction::create([
                        'produit_id' => $produit->id,
                        'user_id' => Auth::id(),
                        'quantite' => $item['quantite'],
                        'total' => $produit->prix * $item['quantite'],
                        'statut' => 'effectuée',
                        'groupe_id' => $groupeId,
                    ]);

                    $produit->decrement('quantite', $item['quantite']);
                }
            });
        } catch (\RuntimeException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        $message = count($items) > 1
            ? count($items).' transactions enregistrées avec succès !'
            : 'Transaction enregistrée avec succès !';

        return redirect()->route('transactions.index')->with('success', $message);
    }

    // Afficher le formulaire d'édition d'une transaction
    public function edit(Transaction $transaction)
    {
        $this->authorize('update', $transaction);

        $produits = Produit::where('user_id', Auth::id())->get();
        return view('transactions.edit', compact('transaction', 'produits'));
    }

    // Mettre à jour une transaction existante
    public function update(UpdateTransactionRequest $request, Transaction $transaction)
    {
        $this->authorize('update', $transaction);

        $data = $request->validated();

        try {
            DB::transaction(function () use ($data, $transaction) {
                $ancienProduitId = $transaction->produit_id;
                $ancienneQuantite = $transaction->quantite;
                $ancienStatut = $transaction->statut;

                // Verrouille l'ancien et le nouveau produit (peuvent être le même)
                // pour éviter toute course avec une autre vente en cours.
                // Re-scope explicitement par utilisateur en plus de la validation
                // (défense en profondeur contre une IDOR sur produit_id).
                $produitIds = collect([$ancienProduitId, $data['produit_id']])->unique();
                $produits = Produit::whereIn('id', $produitIds)
                    ->where('user_id', Auth::id())
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                if ($produits->count() !== $produitIds->count()) {
                    throw new \RuntimeException('Produit introuvable.');
                }

                // Si la transaction avait déjà décrémenté du stock, on le restitue
                // avant d'appliquer les nouvelles valeurs, pour ne jamais décompter deux fois.
                if ($ancienStatut === 'effectuée') {
                    $produits[$ancienProduitId]->increment('quantite', $ancienneQuantite);
                }

                $nouveauProduit = $produits[$data['produit_id']];
                // Recharger au cas où le produit ci-dessus vient d'être crédité
                $nouveauProduit->refresh();

                if ($data['statut'] === 'effectuée' && $nouveauProduit->quantite < $data['quantite']) {
                    throw new \RuntimeException('Stock insuffisant.');
                }

                $transaction->update([
                    'produit_id' => $data['produit_id'],
                    'quantite' => $data['quantite'],
                    'total' => $nouveauProduit->prix * $data['quantite'],
                    'statut' => $data['statut'],
                ]);

                if ($data['statut'] === 'effectuée') {
                    $nouveauProduit->decrement('quantite', $data['quantite']);
                }
            });
        } catch (\RuntimeException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->route('transactions.index')->with('success', 'Transaction mise à jour avec succès !');
    }

    // Annuler une transaction
    public function destroy(Transaction $transaction)
    {
        $this->authorize('delete', $transaction);

        DB::transaction(function () use ($transaction) {
            // Restituer le stock si la transaction avait effectivement décrémenté le produit
            if ($transaction->statut === 'effectuée') {
                Produit::where('id', $transaction->produit_id)
                    ->where('user_id', Auth::id())
                    ->lockForUpdate()
                    ->increment('quantite', $transaction->quantite);
            }

            $transaction->update(['statut' => 'annulée']);
        });

        return redirect()->route('transactions.index')->with('success', 'Transaction annulée.');
    }
}
