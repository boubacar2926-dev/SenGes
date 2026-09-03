<?php

namespace App\Http\Controllers;

use App\Models\AdminActionLog;
use App\Models\User;
use App\Models\Produit;
use App\Models\Transaction;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function index()
    {
        $totalCommercants = User::where('role', 'commercant')->count();
        $totalProduits = Produit::count();
        $totalTransactions = Transaction::where('statut', 'effectuée')->count();
        $revenuTotal = Transaction::where('statut', 'effectuée')->sum('total');

        return view('admin.dashboard', compact('totalCommercants', 'totalProduits', 'totalTransactions', 'revenuTotal'));
    }

    // Journal des actions admin (création/modification/suspension/suppression
    // de commerçants), pour la traçabilité.
    public function journal()
    {
        $logs = AdminActionLog::orderByDesc('created_at')->paginate(20);

        return view('admin.journal', compact('logs'));
    }
}

