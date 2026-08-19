<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Produit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StatistiqueController extends Controller
{
    public function index()
    {
        $userId = Auth::id();

        // Total des ventes
        $totalVentes = Transaction::where('user_id', $userId)->sum('total');

        // Nombre total de transactions
        $nombreTransactions = Transaction::where('user_id', $userId)->count();

        // Produits les plus vendus
        $produitsPopulaires = Transaction::select('produit_id')
            ->where('user_id', $userId)
            ->groupBy('produit_id')
            ->selectRaw('produit_id, SUM(quantite) as total_quantite')
            ->orderByDesc('total_quantite')
            ->limit(5)
            ->with('produit')
            ->get();

        return view('statistiques.index', compact('totalVentes', 'nombreTransactions', 'produitsPopulaires'));
    }
}
