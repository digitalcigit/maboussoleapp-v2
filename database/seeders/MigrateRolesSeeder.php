<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MigrateRolesSeeder extends Seeder
{
    protected $roleMapping = [
        'super-admin' => 'super-admin',
        'admin' => 'manager',        // ancien admin devient manager
        'manager' => 'manager',
        'conseiller' => 'conseiller',
        'assistant' => 'conseiller', // assistant devient conseiller
    ];

    public function run(): void
    {
        Log::info('Début de la migration des rôles');
        
        // 1. Sauvegarde des associations utilisateurs-rôles
        $userRoles = [];
        User::with('roles')->each(function ($user) use (&$userRoles) {
            $userRoles[$user->id] = $user->roles->pluck('name')->toArray();
            Log::info("Sauvegarde des rôles pour l'utilisateur {$user->email}", ['roles' => $userRoles[$user->id]]);
        });

        // 2. Suppression des tables de permissions et rôles
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('model_has_permissions')->truncate();
        DB::table('model_has_roles')->truncate();
        DB::table('role_has_permissions')->truncate();
        DB::table('permissions')->truncate();
        DB::table('roles')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        
        Log::info('Tables de permissions et rôles nettoyées');

        // 3. Création des nouveaux rôles et permissions
        $this->call(RoleAndPermissionSeeder::class);
        Log::info('Nouveaux rôles et permissions créés');

        // 4. Réassignation des rôles aux utilisateurs
        foreach ($userRoles as $userId => $roles) {
            $user = User::find($userId);
            foreach ($roles as $oldRole) {
                $newRole = $this->roleMapping[$oldRole] ?? $oldRole;
                if (Role::where('name', $newRole)->exists()) {
                    $user->assignRole($newRole);
                    Log::info("Rôle {$newRole} réassigné à l'utilisateur {$user->email}");
                } else {
                    Log::warning("Rôle {$newRole} non trouvé pour l'utilisateur {$user->email}");
                }
            }
        }

        Log::info('Migration des rôles terminée avec succès');
    }
}
