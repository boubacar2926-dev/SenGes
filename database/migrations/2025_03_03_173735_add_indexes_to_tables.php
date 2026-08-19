<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('produits', function (Blueprint $table) {
            $table->index('user_id'); // Index sur l'utilisateur pour accélérer les requêtes
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->index('user_id');
            $table->index('produit_id'); // Index sur le produit pour accélérer les jointures
        });
    }

    public function down()
    {
        Schema::table('produits', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'produit_id']);
        });
    }
};

