<?php

namespace Tests\Feature\Performance;

use App\Models\Activity;
use App\Models\Prospect;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ResourceLoadingTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected $startTime;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->startTime = microtime(true);
    }

    protected function tearDown(): void
    {
        $endTime = microtime(true);
        $executionTime = ($endTime - $this->startTime) * 1000; // en millisecondes
        echo "\nTest execution time: {$executionTime}ms\n";
        parent::tearDown();
    }

    /** @test */
    public function prospect_list_loads_within_acceptable_time()
    {
        // Créer 100 prospects avec activités
        Prospect::factory()
            ->count(100)
            ->has(Activity::factory()->count(5))
            ->create();

        $start = microtime(true);

        // Simuler la requête de liste avec pagination
        $response = $this->actingAs($this->admin)
            ->get('/admin/prospects');

        $loadTime = (microtime(true) - $start) * 1000; // en millisecondes

        $this->assertTrue(
            $loadTime < 1000, // max 1 seconde
            "La liste des prospects a mis {$loadTime}ms à charger"
        );

        $response->assertStatus(200);
    }

    /** @test */
    public function prospect_search_performs_within_limits()
    {
        // Créer 500 prospects
        Prospect::factory()->count(500)->create();

        $start = microtime(true);

        // Simuler une recherche
        $response = $this->actingAs($this->admin)
            ->get('/admin/prospects?search=test');

        $searchTime = (microtime(true) - $start) * 1000;

        $this->assertTrue(
            $searchTime < 500, // max 500ms
            "La recherche a mis {$searchTime}ms"
        );

        $response->assertStatus(200);
    }

    /** @test */
    public function activity_list_with_relations_loads_efficiently()
    {
        // Créer 200 activités avec relations
        Activity::factory()
            ->count(200)
            ->for(User::factory())
            ->for(Prospect::factory())
            ->create();

        $start = microtime(true);

        // Charger la liste avec relations
        $response = $this->actingAs($this->admin)
            ->get('/admin/activities');

        $loadTime = (microtime(true) - $start) * 1000;

        $this->assertTrue(
            $loadTime < 1500, // max 1.5 secondes
            "La liste des activités a mis {$loadTime}ms à charger"
        );

        $response->assertStatus(200);
    }

    /** @test */
    public function database_query_optimization_check()
    {
        Prospect::factory()
            ->count(50)
            ->has(Activity::factory()->count(3))
            ->create();

        DB::enableQueryLog();

        // Simuler une requête typique de listing
        $prospects = Prospect::with(['activities', 'user'])
            ->withCount('activities')
            ->paginate(15);

        $queries = DB::getQueryLog();
        $queryCount = count($queries);

        $this->assertLessThan(
            5, // Maximum 4 requêtes attendues
            $queryCount,
            "Trop de requêtes exécutées ({$queryCount}). Vérifier l'optimisation."
        );
    }
}
