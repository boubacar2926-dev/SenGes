<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use App\Models\Produit;
use App\Models\Transaction;
use App\Policies\ProduitPolicy;
use App\Policies\TransactionPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Produit::class => ProduitPolicy::class,
        Transaction::class => TransactionPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // Renforce la politique de mot de passe en production (mixte + vérification
        // contre les fuites connues via l'API "have I been pwned"). On ne durcit pas
        // les règles en local/tests pour ne pas casser les jeux de données existants
        // (ex: mots de passe "password" utilisés dans les tests et fixtures).
        Password::defaults(function () {
            $rule = Password::min(8);

            return $this->app->isProduction()
                ? $rule->mixedCase()->numbers()->uncompromised()
                : $rule;
        });
    }
}
