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
        Schema::table('users', function (Blueprint $table) {
            // Compteur par commerçant : chaque vente prend ce numéro puis
            // l'incrémente (verrouillé via lockForUpdate), pour des factures
            // numérotées #1, #2, #3... propres à chaque commerçant plutôt
            // que basées sur l'id global (partagé entre tous) de la table
            // transactions.
            $table->unsignedInteger('next_invoice_number')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('next_invoice_number');
        });
    }
};
