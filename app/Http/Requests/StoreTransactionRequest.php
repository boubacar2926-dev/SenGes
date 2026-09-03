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
                Rule::exists('produits', 'id')->where(fn ($query) => $query->where('user_id', Auth::id())),
            ],
            'items.*.quantite' => 'required|integer|min:1',
        ];
    }
}
