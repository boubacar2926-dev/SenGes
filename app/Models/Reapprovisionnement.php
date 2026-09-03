<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reapprovisionnement extends Model
{
    use HasFactory;

    public const UPDATED_AT = null;

    protected $fillable = ['produit_id', 'user_id', 'quantite'];

    public function produit()
    {
        return $this->belongsTo(Produit::class)->withTrashed();
    }

    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }
}
