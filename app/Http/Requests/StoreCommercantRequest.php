<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StoreCommercantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            // whereNull('deleted_at') : sans ce filtre, l'email d'un commerçant
            // supprimé (soft delete) resterait bloqué indéfiniment pour un
            // nouveau compte, alors que l'ancien n'est plus accessible.
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')->where(fn ($query) => $query->whereNull('deleted_at')),
            ],
            'password' => ['required', 'confirmed', Password::defaults()],
        ];
    }
}
