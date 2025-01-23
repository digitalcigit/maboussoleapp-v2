<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Dossier;
use App\Models\Prospect;
use Illuminate\Support\Facades\Mail;
use App\Mail\ProspectPortalAccess;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use App\Filament\Resources\DossierResource\Pages\CreateDossier;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Services\ReferenceGeneratorService;

class DossierCreationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_creates_prospect_user_and_sends_email_when_creating_dossier()
    {
        // 1. Intercepter les emails
        Mail::fake();

        // 2. Créer les permissions et rôles nécessaires
        $adminRole = Role::create(['name' => 'admin']);
        $candidatRole = Role::create(['name' => 'portail_candidat']);
        
        // Créer toutes les permissions nécessaires
        $permissions = [
            'dossiers.create',
            'dossiers.view',
            'dossiers.update',
            'dossiers.delete',
            'dossiers.list'
        ];
        
        foreach ($permissions as $permissionName) {
            $permission = Permission::create(['name' => $permissionName]);
            $adminRole->givePermissionTo($permission);
        }

        // 3. Créer un utilisateur admin
        $admin = User::factory()->create(['email' => 'admin@digital.ci']);
        $admin->assignRole('admin');
        $this->actingAs($admin);

        // 4. Données de test
        $referenceGenerator = app(ReferenceGeneratorService::class);
        $dossierData = [
            'reference_number' => 'DOS-' . $referenceGenerator->generateReference('dossier'),
            'prospect_info' => [
                'first_name' => 'Test',
                'last_name' => 'Candidat',
                'email' => 'test.candidat@example.com',
                'phone' => '0123456789',
                'profession' => 'Développeur',
                'education_level' => 'BAC+5',
                'desired_field' => 'IT',
                'desired_destination' => 'Canada',
            ],
            'current_step' => Dossier::STEP_ANALYSIS,
            'current_status' => Dossier::STATUS_WAITING_DOCS,
            'assigned_to' => $admin->id
        ];

        // 5. Créer le dossier via Livewire
        Livewire::test(CreateDossier::class)
            ->set('data', $dossierData)
            ->call('create')
            ->assertHasNoErrors();

        // 6. Vérifications
        $this->assertDatabaseHas('prospects', [
            'email' => 'test.candidat@example.com',
            'first_name' => 'Test',
            'last_name' => 'Candidat',
        ]);

        $prospect = Prospect::where('email', 'test.candidat@example.com')->first();
        $this->assertNotNull($prospect);

        $this->assertDatabaseHas('users', [
            'email' => 'test.candidat@example.com',
        ]);

        $user = User::where('email', 'test.candidat@example.com')->first();
        $this->assertNotNull($user);
        $this->assertTrue($user->hasRole('portail_candidat'));

        // 7. Vérifier l'envoi de l'email
        Mail::assertSent(ProspectPortalAccess::class, function ($mail) use ($user) {
            return $mail->hasTo('test.candidat@example.com');
        });
    }
}
