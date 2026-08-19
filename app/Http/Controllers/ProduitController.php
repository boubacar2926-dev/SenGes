<?php

namespace App\Http\Controllers;

use App\Models\Produit;
use Illuminate\Http\Request;
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

    public function store(Request $request)
    {
        $request->validate([
            'nom' => 'required|string|max:255',
            'description' => 'nullable|string',
            'prix' => 'required|numeric|min:0',
            'quantite' => 'required|integer|min:1',
        ]);

        Produit::create([
            'nom' => $request->nom,
            'description' => $request->description,
            'prix' => $request->prix,
            'quantite' => $request->quantite,
            'user_id' => Auth::id(),
        ]);

        return redirect()->route('produits.index')->with('success', 'Produit ajouté avec succès !');
    }

    public function edit(Produit $produit)
    {
        if ($produit->user_id !== Auth::id()) {
            abort(403, 'Accès interdit');
        }
        return view('produits.edit', compact('produit'));
    }

    public function update(Request $request, Produit $produit)
    {
        if ($produit->user_id !== Auth::id()) {
            abort(403, 'Accès interdit');
        }

        $request->validate([
            'nom' => 'required|string|max:255',
            'description' => 'nullable|string',
            'prix' => 'required|numeric|min:0',
            'quantite' => 'required|integer|min:1',
        ]);

        $produit->update($request->all());

        return redirect()->route('produits.index')->with('success', 'Produit mis à jour !');
    }

    public function destroy(Produit $produit)
    {
        if ($produit->user_id !== Auth::id()) {
            abort(403, 'Accès interdit');
        }

        $produit->delete();
        return redirect()->route('produits.index')->with('success', 'Produit supprimé !');
    }

    

}
