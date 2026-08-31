<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTransactionRequest;
use App\Http\Requests\UpdateTransactionRequest;
use App\Models\Transaction;
use App\Models\Produit;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    // Afficher la liste des transactions
    public function index()
    {
        $transactions = Transaction::where('user_id', Auth::id())->paginate(10);
        return view('transactions.index', compact('transactions'));
    }

    // Afficher le formulaire de création d'une transaction
    public function create()
    {
        $produits = Produit::where('user_id', Auth::id())->get();
        return view('transactions.create', compact('produits'));
    }

    // Enregistrer une nouvelle transaction
    public function store(StoreTransactionRequest $request)
    {
        $data = $request->validated();

        try {
            DB::transaction(function () use ($data) {
                // Verrouille la ligne pour éviter qu'une requête concurrente
                // ne vende le même stock en même temps.
                $produit = Produit::where('id', $data['produit_id'])
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($produit->quantite < $data['quantite']) {
                    throw new \RuntimeException('Stock insuffisant.');
                }

                Transaction::create([
                    'produit_id' => $produit->id,
                    'user_id' => Auth::id(),
                    'quantite' => $data['quantite'],
                    'total' => $produit->prix * $data['quantite'],
                    'statut' => 'effectuée',
                ]);

                $produit->decrement('quantite', $data['quantite']);
            });
        } catch (\RuntimeException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->route('transactions.index')->with('success', 'Transaction enregistrée avec succès !');
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
                $produitIds = collect([$ancienProduitId, $data['produit_id']])->unique();
                $produits = Produit::whereIn('id', $produitIds)->lockForUpdate()->get()->keyBy('id');

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
                    ->lockForUpdate()
                    ->increment('quantite', $transaction->quantite);
            }

            $transaction->update(['statut' => 'annulée']);
        });

        return redirect()->route('transactions.index')->with('success', 'Transaction annulée.');
    }
}
