<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->uuid('groupe_id')->nullable()->after('statut');
            $table->index('groupe_id');
        });

        // Les transactions déjà existantes n'ont pas de groupe : chacune
        // devient son propre groupe d'une seule ligne pour continuer à
        // pouvoir afficher une facture individuelle.
        DB::table('transactions')->select('id')->orderBy('id')->get()->each(function ($row) {
            DB::table('transactions')->where('id', $row->id)->update(['groupe_id' => (string) Str::uuid()]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex(['groupe_id']);
            $table->dropColumn('groupe_id');
        });
    }
};
