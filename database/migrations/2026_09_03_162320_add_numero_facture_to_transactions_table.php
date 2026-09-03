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
        Schema::table('transactions', function (Blueprint $table) {
            // Nullable : les ventes existantes n'ont pas de numéro attribué
            // rétroactivement (la facture retombe alors sur l'ancien numéro
            // basé sur l'id, voir transactions/facture.blade.php).
            $table->unsignedInteger('numero_facture')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn('numero_facture');
        });
    }
};
