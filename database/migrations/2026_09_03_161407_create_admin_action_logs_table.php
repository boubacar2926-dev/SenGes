<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('admin_action_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->constrained('users');
            // Nom de l'admin au moment de l'action : reste lisible même si son
            // compte est renommé ou supprimé (soft delete) plus tard.
            $table->string('admin_name');
            // 'creation', 'modification', 'suspension', 'reactivation', 'suppression'
            $table->string('action');
            $table->foreignId('target_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('target_name');
            $table->string('target_email');
            $table->text('details')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_action_logs');
    }
};
