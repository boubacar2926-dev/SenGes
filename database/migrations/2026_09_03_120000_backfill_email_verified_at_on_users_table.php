<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Le modèle User implémente désormais MustVerifyEmail, ce qui active
     * réellement le middleware "verified" (jusque-là un no-op silencieux).
     * Sans ce backfill, tout compte existant créé avant ce changement (via
     * seeder ou inscription) et n'ayant jamais "vérifié" son email serait
     * immédiatement bloqué hors de /dashboard au prochain déploiement —
     * y compris le compte admin actif en production.
     */
    public function up(): void
    {
        DB::table('users')
            ->whereNull('email_verified_at')
            ->update(['email_verified_at' => DB::raw('created_at')]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Irréversible par nature (on ne sait plus qui était vérifié avant) :
        // pas de rollback destructif ici, volontairement no-op.
    }
};
