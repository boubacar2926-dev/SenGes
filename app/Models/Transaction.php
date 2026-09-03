<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = ['produit_id', 'user_id', 'quantite', 'total', 'statut', 'groupe_id', 'numero_facture'];

    public function produit()
    {
        // withTrashed : un produit peut avoir été supprimé (soft delete)
        // depuis, mais son historique de vente/facture doit continuer à
        // afficher son nom et son prix au lieu d'une relation vide.
        return $this->belongsTo(Produit::class)->withTrashed();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

