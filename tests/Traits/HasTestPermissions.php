<?php

namespace Tests\Traits;

use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;

trait HasTestPermissions
{
    protected User $user;

    protected function setUpHasTestPermissions(): void
    {
        // Crée un utilisateur de test avec un email unique
        $this->user = User::factory()->create([
            'email' => 'test_' . uniqid() . '@example.com',
            'password' => Hash::make('password'),
        ]);

        // Authentifie l'utilisateur
        $this->actingAs($this->user);

        // Configure les permissions
        $this->setupPermissions();
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpHasTestPermissions();
    }

    protected function setupPermissions(): void
    {
        // Supprime toutes les permissions existantes
        Role::query()->delete();
        Permission::query()->delete();

        // Crée les permissions de base
        $permissions = [
            // Permissions générales Filament
            'access_filament',
            'access_admin_panel',
            'view_admin_panel',
            
            // Permissions pour les activités
            'activities.view',
            'activities.create',
            'activities.edit',
            'activities.delete',
            'activities.view_any',  // Permission Filament
            'activities.update',    // Permission Filament
            'manage activities',    // Permission Filament
            
            // Permissions pour les clients
            'clients.view',
            'clients.create',
            'clients.edit',
            'clients.delete',
            'clients.view_any',     // Permission Filament
            'clients.update',       // Permission Filament
            'manage clients',       // Permission Filament
            
            // Permissions pour les prospects
            'prospects.view',
            'prospects.create',
            'prospects.edit',
            'prospects.delete',
            'prospects.convert',
            'prospects.assign',
            'prospects.view_any',   // Permission Filament
            'prospects.update',     // Permission Filament
            'prospects.bulk_delete', // Permission pour la suppression en masse
            'manage prospects'      // Permission Filament
        ];

        // Crée les permissions dans la base de données
        $createdPermissions = [];
        foreach ($permissions as $permission) {
            $createdPermissions[] = Permission::firstOrCreate(['name' => $permission]);
        }

        // Crée les rôles principaux
        $roles = ['super-admin', 'manager', 'conseiller', 'partenaire', 'commercial'];
        foreach ($roles as $roleName) {
            Role::firstOrCreate(['name' => $roleName]);
        }

        // Donne toutes les permissions au rôle manager
        $managerRole = Role::findByName('manager');
        $managerRole->syncPermissions(Permission::all());

        // Crée un rôle admin avec toutes les permissions
        $role = Role::firstOrCreate(['name' => 'admin']);
        $role->syncPermissions($createdPermissions);

        // Assigne le rôle admin à l'utilisateur de test
        $this->user->assignRole('admin');
        
        // Donne directement les permissions à l'utilisateur
        $this->user->syncPermissions($createdPermissions);
    }

    /**
     * Helper pour les requêtes Filament
     */
    protected function filament(string $method, string $uri, array $data = [])
    {
        $method = strtoupper($method);
        
        // Filament utilise toujours POST avec _method pour les autres méthodes
        $actualMethod = 'POST';
        if (in_array($method, ['PUT', 'PATCH', 'DELETE'])) {
            $data['_method'] = $method;
        }

        // Ajouter les en-têtes Filament nécessaires
        return $this->withHeaders([
            'X-Filament-Context' => 'filament',
            'X-Requested-With' => 'XMLHttpRequest',
            'X-CSRF-TOKEN' => csrf_token(),
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->$actualMethod($uri, $data);
    }
}
