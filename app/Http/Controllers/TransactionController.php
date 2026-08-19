<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Produit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
    public function store(Request $request)
    {
        $request->validate([
            'produit_id' => 'required|exists:produits,id',
            'quantite' => 'required|integer|min:1',
        ]);

        $produit = Produit::findOrFail($request->produit_id);
        $total = $produit->prix * $request->quantite;

        if ($produit->quantite < $request->quantite) {
            return redirect()->back()->with('error', 'Stock insuffisant.');
        }

        // Enregistrer la transaction
        Transaction::create([
            'produit_id' => $request->produit_id,
            'user_id' => Auth::id(),
            'quantite' => $request->quantite,
            'total' => $total,
            'statut' => 'effectuée',
        ]);

        // Mettre à jour le stock
        $produit->decrement('quantite', $request->quantite);

        return redirect()->route('transactions.index')->with('success', 'Transaction enregistrée avec succès !');
    }

    // Afficher le formulaire d'édition d'une transaction
    public function edit(Transaction $transaction)
    {
        // Vérifier que l'utilisateur est autorisé à modifier cette transaction
        if ($transaction->user_id !== Auth::id()) {
            abort(403, 'Accès interdit');
        }

        $produits = Produit::where('user_id', Auth::id())->get();
        return view('transactions.edit', compact('transaction', 'produits'));
    }

    // Mettre à jour une transaction existante
    public function update(Request $request, Transaction $transaction)
    {
        // Vérifier que l'utilisateur est autorisé à modifier cette transaction
        if ($transaction->user_id !== Auth::id()) {
            abort(403, 'Accès interdit');
        }

        $request->validate([
            'produit_id' => 'required|exists:produits,id',
            'quantite' => 'required|integer|min:1',
            'statut' => 'required|in:en attente,effectuée,annulée',
        ]);

        // Récupérer le produit associé
        $produit = Produit::findOrFail($request->produit_id);

        // Calculer le nouveau total
        $total = $produit->prix * $request->quantite;

        // Vérifier le stock si la transaction est "effectuée"
        if ($request->statut === 'effectuée' && $produit->quantite < $request->quantite) {
            return redirect()->back()->with('error', 'Stock insuffisant.');
        }

        // Mettre à jour la transaction
        $transaction->update([
            'produit_id' => $request->produit_id,
            'quantite' => $request->quantite,
            'total' => $total,
            'statut' => $request->statut,
        ]);

        // Mettre à jour le stock si nécessaire
        if ($request->statut === 'effectuée') {
            $produit->decrement('quantite', $request->quantite);
        }

        return redirect()->route('transactions.index')->with('success', 'Transaction mise à jour avec succès !');
    }

    // Annuler une transaction
    public function destroy(Transaction $transaction)
    {
        if ($transaction->user_id !== Auth::id()) {
            abort(403, 'Accès interdit');
        }

        $transaction->update(['statut' => 'annulée']);
        return redirect()->route('transactions.index')->with('success', 'Transaction annulée.');
    }
}
