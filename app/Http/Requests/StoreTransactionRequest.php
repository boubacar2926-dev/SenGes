<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StoreTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'items' => 'required|array|min:1|max:100',
            // Le produit doit exister ET appartenir au commerçant connecté :
            // sans ce filtre, un utilisateur pourrait passer l'id du produit
            // d'un autre commerçant et faire décrémenter son stock à lui.
            'items.*.produit_id' => [
                'required',
                // whereNull('deleted_at') : Rule::exists interroge la table
                // directement (hors Eloquent), donc ignore par défaut le
                // scope global de soft delete — sans ce filtre, un produit
                // supprimé resterait "vendable" via ce endpoint.
                Rule::exists('produits', 'id')->where(
                    fn ($query) => $query->where('user_id', Auth::id())->whereNull('deleted_at')
                ),
            ],
            'items.*.quantite' => 'required|integer|min:1',
        ];
    }
}
