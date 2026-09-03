<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Vérifié ici (avant les règles de validation ci-dessous) pour que la
        // tentative de modifier la transaction d'un autre commerçant échoue
        // avec un 403 (autorisation), plutôt qu'avec une 422 de validation
        // qui laisserait croire que seul le produit_id posait problème.
        $transaction = $this->route('transaction');

        return $transaction !== null && $transaction->user_id === Auth::id();
    }

    public function rules(): array
    {
        return [
            // Le produit doit appartenir au commerçant connecté : sans ce
            // filtre, on pourrait ré-affecter une transaction existante à un
            // produit appartenant à un autre commerçant (vol/altération de stock).
            'produit_id' => [
                'required',
                // whereNull('deleted_at') : voir StoreTransactionRequest, même
                // raison (Rule::exists ignore le scope global de soft delete).
                Rule::exists('produits', 'id')->where(
                    fn ($query) => $query->where('user_id', Auth::id())->whereNull('deleted_at')
                ),
            ],
            'quantite' => 'required|integer|min:1',
            // La colonne DB n'autorise que ces deux valeurs (voir migration
            // create_transactions_table) ; "en attente" n'existe pas côté DB.
            'statut' => 'required|in:effectuée,annulée',
        ];
    }
}
