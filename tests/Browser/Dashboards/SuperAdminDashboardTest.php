<?php

namespace Tests\Browser\Dashboards;

use App\Models\User;
use App\Models\Client;
use App\Models\Prospect;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class SuperAdminDashboardTest extends DuskTestCase
{
    use DatabaseMigrations;

    protected User $superAdmin;

    public function setUp(): void
    {
        parent::setUp();
        $this->superAdmin = User::factory()->create(['email' => 'super-admin@maboussole.fr']);
        $this->superAdmin->assignRole('super-admin');
        $this->seedTestData();
    }

    protected function seedTestData(): void
    {
        Prospect::factory()->count(50)->create();
        Client::factory()->count(20)->create([
            'status' => 'completed',
            'contract_value' => rand(5000, 50000),
        ]);
    }

    /** @test */
    public function dashboard_displays_correctly()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->superAdmin)
                   ->visit('/admin')
                   ->waitFor('@stats-overview-widget');

            // Test du dashboard complet
            $this->percy($browser, 'dashboard-overview', [
                'widths' => [1280]  // Desktop d'abord
            ]);

            // Test responsive
            $this->percyResponsive($browser, 'dashboard-responsive');
        });
    }

    /** @test */
    public function dashboard_shows_empty_states()
    {
        // Vider la base
        Client::query()->delete();
        Prospect::query()->delete();

        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->superAdmin)
                   ->visit('/admin')
                   ->waitFor('@stats-overview-widget');

            // Capturer les états vides
            $this->percy($browser, 'dashboard-empty');
        });
    }

    /** @test */
    public function dashboard_handles_interactions()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->superAdmin)
                   ->visit('/admin')
                   ->waitFor('@stats-overview-widget');

            // État initial
            $this->percy($browser, 'dashboard-initial');

            // Après filtrage
            $browser->click('@filter-status')
                   ->click('@filter-completed')
                   ->waitForTextIn('@filtered-count', '20');
            $this->percy($browser, 'dashboard-filtered');

            // Après tri
            $browser->click('@sort-amount')
                   ->waitForTextIn('@first-row', '50.000');
            $this->percy($browser, 'dashboard-sorted');
        });
    }
}
