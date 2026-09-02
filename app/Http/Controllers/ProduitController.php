<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProduitRequest;
use App\Models\Produit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ProduitController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $sort = $request->input('sort', 'created_at');
        $direction = $request->input('direction') === 'asc' ? 'asc' : 'desc';

        if (! in_array($sort, ['nom', 'prix', 'quantite', 'created_at'], true)) {
            $sort = 'created_at';
        }

        $matchingIds = Produit::searchIdsForUser(Auth::id(), $search);

        $produits = Produit::where('user_id', Auth::id())
            ->when($matchingIds !== null, fn ($query) => $query->whereIn('id', $matchingIds))
            ->orderBy($sort, $direction)
            ->paginate(10)
            ->withQueryString();

        return view('produits.index', [
            'produits' => $produits,
            'search' => $search,
            'sort' => $sort,
            'direction' => $direction,
        ]);
    }

    public function create()
    {
        return view('produits.create');
    }

    // Suggestions de noms de produits pour l'autocomplétion des recherches
    public function suggestions(Request $request)
    {
        $search = trim((string) $request->input('q', ''));

        if ($search === '') {
            return response()->json([]);
        }

        $needle = Str::of($search)->ascii()->lower()->value();

        $noms = Produit::where('user_id', Auth::id())
            ->pluck('nom')
            ->filter(fn ($nom) => str_contains(Str::of($nom)->ascii()->lower()->value(), $needle))
            ->unique()
            ->sort()
            ->take(8)
            ->values();

        return response()->json($noms);
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
