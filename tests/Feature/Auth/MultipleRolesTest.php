<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MultipleRolesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
    }

    /**
     * Test qu'un utilisateur peut avoir plusieurs rôles
     */
    public function test_user_can_have_multiple_roles(): void
    {
        $user = User::factory()->create();
        
        // Assigner deux rôles
        $user->assignRole(['conseiller', 'commercial']);

        $this->assertTrue($user->hasRole('conseiller'));
        $this->assertTrue($user->hasRole('commercial'));
        $this->assertFalse($user->hasRole('manager'));
    }

    /**
     * Test que les permissions sont cumulatives entre les rôles
     */
    public function test_permissions_are_cumulative(): void
    {
        $user = User::factory()->create();
        
        // Assigner conseiller et commercial
        $user->assignRole(['conseiller', 'commercial']);

        // Permissions qui devraient être présentes car dans les deux rôles
        $this->assertTrue($user->can('prospects.view'));
        $this->assertTrue($user->can('prospects.create'));
        $this->assertTrue($user->can('activities.view'));
        $this->assertTrue($user->can('activities.create'));

        // Permissions uniquement du conseiller
        $this->assertTrue($user->can('clients.view'));
        $this->assertTrue($user->can('documents.validate'));
        $this->assertTrue($user->can('communications.email'));

        // Permissions uniquement du commercial
        $this->assertTrue($user->can('bonus.view.own'));
        $this->assertTrue($user->can('reports.view.own.basic'));

        // Permissions qui ne devraient pas être présentes
        $this->assertFalse($user->can('users.view'));
        $this->assertFalse($user->can('settings.view'));
    }

    /**
     * Test qu'un rôle supérieur englobe les permissions d'un rôle inférieur
     */
    public function test_higher_role_encompasses_lower_role_permissions(): void
    {
        $user = User::factory()->create();
        
        // Assigner manager et conseiller
        $user->assignRole(['manager', 'conseiller']);

        // Le manager devrait avoir toutes les permissions du conseiller plus les siennes
        $this->assertTrue($user->can('users.view')); // Permission manager
        $this->assertTrue($user->can('prospects.delete')); // Permission manager
        $this->assertTrue($user->can('communications.email')); // Permission conseiller
        $this->assertTrue($user->can('documents.upload')); // Permission conseiller

        // Mais pas les permissions d'autres rôles
        $this->assertFalse($user->can('bonus.view.own')); // Permission commercial
    }

    /**
     * Test de la révocation d'un rôle tout en gardant l'autre
     */
    public function test_role_revocation_maintains_other_role_permissions(): void
    {
        $user = User::factory()->create();
        
        // Assigner deux rôles
        $user->assignRole(['conseiller', 'commercial']);

        // Vérifier les permissions initiales
        $this->assertTrue($user->can('clients.view')); // Permission conseiller
        $this->assertTrue($user->can('bonus.view.own')); // Permission commercial

        // Révoquer le rôle conseiller
        $user->removeRole('conseiller');

        // Ne devrait plus avoir les permissions conseiller
        $this->assertFalse($user->can('clients.view'));
        $this->assertFalse($user->can('communications.email'));

        // Mais devrait toujours avoir les permissions commercial
        $this->assertTrue($user->can('bonus.view.own'));
        $this->assertTrue($user->can('reports.view.own.basic'));
    }

    /**
     * Test des conflits potentiels de permissions entre rôles
     */
    public function test_permission_conflicts_between_roles(): void
    {
        $user = User::factory()->create();
        
        // Créer un nouveau rôle avec des permissions restrictives
        $restrictedRole = Role::create(['name' => 'restricted']);
        $restrictedRole->givePermissionTo([
            'prospects.view',
            'reports.view.own.basic'
        ]);

        // Assigner les rôles commercial et restricted
        $user->assignRole(['commercial', 'restricted']);

        // Même avec le rôle restrictif, l'utilisateur devrait avoir toutes les permissions
        // car les permissions sont additives
        $this->assertTrue($user->can('prospects.create'));
        $this->assertTrue($user->can('activities.create'));
        $this->assertTrue($user->can('bonus.view.own'));
    }

    /**
     * Test de la hiérarchie des rôles avec super-admin
     */
    public function test_super_admin_role_overrides_all(): void
    {
        $user = User::factory()->create();
        
        // Assigner d'abord des rôles restrictifs
        $user->assignRole(['conseiller', 'commercial']);

        // Puis ajouter super-admin
        $user->assignRole('super-admin');

        // Devrait avoir accès à tout
        $this->assertTrue($user->can('system.settings.view'));
        $this->assertTrue($user->can('system.logs.view'));
        $this->assertTrue($user->can('users.view'));
        $this->assertTrue($user->can('settings.edit.department'));

        // Même après avoir retiré les autres rôles
        $user->removeRole('conseiller');
        $user->removeRole('commercial');
        $this->assertTrue($user->can('system.settings.view'));
        $this->assertTrue($user->can('prospects.view'));
        $this->assertTrue($user->can('clients.view'));
    }
}
