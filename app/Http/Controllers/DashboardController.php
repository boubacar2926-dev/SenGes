<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $userId = Auth::id();

        // Récupérer les revenus des 7 derniers jours (hors transactions annulées)
        $revenusParJour = Transaction::where('user_id', $userId)
            ->where('statut', 'effectuée')
            ->where('created_at', '>=', now()->subDays(7))
            ->selectRaw('DATE(created_at) as date, SUM(total) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Récupérer les produits les plus vendus
        $produitsPopulaires = Transaction::where('user_id', $userId)
            ->where('statut', 'effectuée')
            ->selectRaw('produit_id, SUM(quantite) as total_vendu')
            ->groupBy('produit_id')
            ->orderByDesc('total_vendu')
            ->limit(5)
            ->with('produit')
            ->get();

        return view('dashboard', compact('revenusParJour', 'produitsPopulaires'));
    }
}

