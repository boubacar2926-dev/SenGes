<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Produit extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['nom', 'description', 'prix', 'quantite', 'user_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reapprovisionnements()
    {
        return $this->hasMany(Reapprovisionnement::class);
    }

    /**
     * Ids des produits d'un utilisateur dont le nom correspond à la
     * recherche, sans tenir compte de la casse ni des accents. Ne dépend
     * d'aucune fonction spécifique à MySQL ou PostgreSQL (LIKE se comporte
     * différemment sur les deux : sensible à la casse sur Postgres).
     *
     * @return array<int>|null null si aucune recherche n'est fournie
     */
    public static function searchIdsForUser(int $userId, ?string $search): ?array
    {
        $needle = Str::of((string) $search)->ascii()->lower()->trim()->value();

        if ($needle === '') {
            return null;
        }

        return static::where('user_id', $userId)
            ->get(['id', 'nom'])
            ->filter(fn (self $produit) => str_contains(
                Str::of($produit->nom)->ascii()->lower()->value(),
                $needle
            ))
            ->pluck('id')
            ->all();
    }
}

