<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('produits', function (Blueprint $table) {
            // Soft delete : un produit déjà vendu ne doit jamais être
            // effacé physiquement, sinon la contrainte onDelete('cascade')
            // de transactions.produit_id efface l'historique des ventes
            // et fausse les statistiques/factures passées.
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('produits', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
