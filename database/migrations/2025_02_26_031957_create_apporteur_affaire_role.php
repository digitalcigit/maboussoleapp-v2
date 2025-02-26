<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Créer le rôle apporteur d'affaire
        $role = Role::create(['name' => 'apporteur-affaire']);

        // Ajouter les permissions nécessaires pour un apporteur d'affaire
        $permissions = [
            'view_own_prospects',
            'create_prospects',
            'edit_own_prospects',
            'delete_own_prospects',
        ];

        foreach ($permissions as $permission) {
            // Créer la permission si elle n'existe pas
            Permission::firstOrCreate(['name' => $permission]);
            // Assigner la permission au rôle
            $role->givePermissionTo($permission);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Supprimer le rôle et ses permissions associées
        $role = Role::where('name', 'apporteur-affaire')->first();
        if ($role) {
            $role->delete();
        }
    }
};
