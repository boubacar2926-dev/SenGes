<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReapprovisionnementRequest;
use App\Models\Produit;
use App\Models\Reapprovisionnement;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReapprovisionnementController extends Controller
{
    // Historique des réapprovisionnements d'un produit + formulaire pour en ajouter un
    public function index(Produit $produit)
    {
        $this->authorize('update', $produit);

        $reapprovisionnements = $produit->reapprovisionnements()
            ->with('user')
            ->orderByDesc('created_at')
            ->get();

        return view('produits.reapprovisionnements', compact('produit', 'reapprovisionnements'));
    }

    public function store(StoreReapprovisionnementRequest $request, Produit $produit)
    {
        $this->authorize('update', $produit);

        $quantite = $request->validated()['quantite'];

        DB::transaction(function () use ($produit, $quantite) {
            Reapprovisionnement::create([
                'produit_id' => $produit->id,
                'user_id' => Auth::id(),
                'quantite' => $quantite,
            ]);

            $produit->increment('quantite', $quantite);
        });

        return redirect()
            ->route('produits.reapprovisionnements.index', $produit)
            ->with('success', "Stock mis à jour : +{$quantite}.");
    }
}
