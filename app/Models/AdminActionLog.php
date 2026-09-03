<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminActionLog extends Model
{
    use HasFactory;

    public const UPDATED_AT = null;

    protected $fillable = [
        'admin_id',
        'admin_name',
        'action',
        'target_user_id',
        'target_name',
        'target_email',
        'details',
    ];

    /**
     * Enregistre une action admin sur un compte commerçant. Le nom/email de
     * l'admin et de la cible sont dupliqués sur la ligne (pas seulement liés
     * par relation) pour que le journal reste lisible même après un
     * changement de nom ou une suppression (soft delete) ultérieure.
     */
    public static function record(User $admin, string $action, User $target, ?string $details = null): self
    {
        return static::create([
            'admin_id' => $admin->id,
            'admin_name' => $admin->name,
            'action' => $action,
            'target_user_id' => $target->id,
            'target_name' => $target->name,
            'target_email' => $target->email,
            'details' => $details,
        ]);
    }
}
