<?php

namespace App\Http\Controllers;

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
        $totalTransactions = Transaction::count();
        $revenuTotal = Transaction::sum('total');

        return view('admin.dashboard', compact('totalCommercants', 'totalProduits', 'totalTransactions', 'revenuTotal'));
    }
}

