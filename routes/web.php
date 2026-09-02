<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\ProduitController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CommercantController;
use App\Http\Controllers\StatistiqueController;
use App\Http\Controllers\TransactionController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Routes de l'application pour les utilisateurs authentifiés
|
*/

// Route d'accueil
Route::get('/', function () {
    return view('welcome');

});

// Gestion du profil utilisateur (accessible à tous les utilisateurs connectés)
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

/*
|--------------------------------------------------------------------------
| Routes Admin
|--------------------------------------------------------------------------
|
| Ces routes sont réservées aux administrateurs.
|
*/
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/dashboard', [AdminController::class, 'index'])->name('admin.dashboard');
    Route::resource('admin/users', AdminUserController::class)
    ->except(['show'])
    ->names([
        'index' => 'admin.users.index',
        'create' => 'admin.users.create',
        'store' => 'admin.users.store',
        'edit' => 'admin.users.edit',
        'update' => 'admin.users.update',
        'destroy' => 'admin.users.destroy'
    ]);

    Route::post('admin/users/{user}/suspend', [AdminUserController::class, 'suspend'])->name('admin.users.suspend');
});



/*
|--------------------------------------------------------------------------
| Routes Commerçants
|--------------------------------------------------------------------------
|
| Ces routes sont réservées aux commerçants.
|
*/
Route::middleware(['auth', 'role:commercant'])->group(function () {
    Route::get('/commercant/dashboard', [CommercantController::class, 'index'])->name('commercant.dashboard');
    Route::resource('produits', ProduitController::class)->except(['show']);

    // Routes pour les transactions
    Route::resource('transactions', TransactionController::class)->except(['show']);
    Route::get('transactions/{transaction}/facture', [TransactionController::class, 'facture'])->name('transactions.facture');

    Route::get('/statistiques', [StatistiqueController::class, 'index'])->name('statistiques.index');



});

// Route du dashboard (accessible après connexion, contenu selon le rôle)
Route::middleware(['auth', 'verified', 'role:admin,commercant'])->get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');


require __DIR__.'/auth.php';
