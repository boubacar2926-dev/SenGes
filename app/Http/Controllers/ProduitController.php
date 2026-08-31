<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProduitRequest;
use App\Models\Produit;
use Illuminate\Support\Facades\Auth;

class ProduitController extends Controller
{
    public function index()
    {
        $produits = Produit::where('user_id', Auth::id())->paginate(10);
        return view('produits.index', compact('produits'));
    }

    public function create()
    {
        return view('produits.create');
    }

    public function store(ProduitRequest $request)
    {
        Produit::create([
            ...$request->validated(),
            'user_id' => Auth::id(),
        ]);

        return redirect()->route('produits.index')->with('success', 'Produit ajouté avec succès !');
    }

    public function edit(Produit $produit)
    {
        $this->authorize('update', $produit);

        return view('produits.edit', compact('produit'));
    }

    public function update(ProduitRequest $request, Produit $produit)
    {
        $this->authorize('update', $produit);

        $produit->update($request->validated());

        return redirect()->route('produits.index')->with('success', 'Produit mis à jour !');
    }

    public function destroy(Produit $produit)
    {
        $this->authorize('delete', $produit);

        $produit->delete();
        return redirect()->route('produits.index')->with('success', 'Produit supprimé !');
    }
}
