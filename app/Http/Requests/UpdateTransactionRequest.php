<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'produit_id' => 'required|exists:produits,id',
            'quantite' => 'required|integer|min:1',
            'statut' => 'required|in:en attente,effectuée,annulée',
        ];
    }
}
