# Guide de Test du Tableau de Bord Super Admin

## Tests Unitaires

### Configuration

```php
// tests/Unit/Widgets/FinancialMetricsWidgetTest.php

class FinancialMetricsWidgetTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(
            User::factory()->create()->assignRole('super-admin')
        );
    }
}
```

### Exemples de Tests

```php
public function test_calculates_current_month_revenue_correctly()
{
    // Arrange
    Client::factory()->count(3)->create([
        'total_amount' => 1000,
        'created_at' => now()
    ]);

    // Act
    $widget = new FinancialMetricsWidget();
    $stats = $widget->getStats();

    // Assert
    $this->assertEquals(3000, $stats[0]->getValue());
}

public function test_commission_calculation()
{
    // Arrange
    Client::factory()->create([
        'total_amount' => 1000,
        'created_at' => now()
    ]);

    // Act
    $widget = new FinancialMetricsWidget();
    $stats = $widget->getStats();

    // Assert
    $this->assertEquals(200, $stats[1]->getValue());
}
```

## Tests d'Intégration

### Configuration

```php
// tests/Feature/SuperAdminDashboardTest.php

class SuperAdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create()->assignRole('super-admin');
        $this->actingAs($this->user);
    }
}
```

### Exemples de Tests

```php
public function test_dashboard_displays_all_widgets()
{
    // Act
    $response = $this->get('/admin/dashboard');

    // Assert
    $response->assertStatus(200)
        ->assertSee('Métriques Financières')
        ->assertSee('Entonnoir de Conversion')
        ->assertSee('Performance Financière')
        ->assertSee('Objectifs Mensuels')
        ->assertSee('Dernières Transactions');
}

public function test_widgets_refresh_automatically()
{
    // Arrange
    $this->get('/admin/dashboard');
    
    // Act
    Client::factory()->create([
        'total_amount' => 1000,
        'created_at' => now()
    ]);

    // Assert via Livewire
    Livewire::test(FinancialMetricsWidget::class)
        ->assertSee('1 000 €');
}
```

## Tests Visuels avec Laravel Dusk

### Configuration

```php
// tests/Browser/SuperAdminDashboardTest.php

class SuperAdminDashboardTest extends DuskTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::factory()->create()->assignRole('super-admin'));
        });
    }
}
```

### Exemples de Tests

```php
public function test_charts_render_correctly()
{
    $this->browse(function (Browser $browser) {
        $browser->visit('/admin/dashboard')
            ->waitFor('.financial-performance-chart')
            ->assertPresent('.chart-canvas')
            ->assertPresent('.prospect-funnel')
            ->screenshot('dashboard-charts');
    });
}

public function test_responsive_layout()
{
    $this->browse(function (Browser $browser) {
        $browser->resize(1920, 1080)
            ->visit('/admin/dashboard')
            ->assertPresent('.grid-cols-4')
            ->resize(768, 1024)
            ->assertPresent('.grid-cols-2')
            ->resize(375, 812)
            ->assertPresent('.grid-cols-1');
    });
}
```

## Tests de Performance

### Configuration

```php
// tests/Performance/DashboardLoadTest.php

class DashboardLoadTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        // Générer des données de test
        Client::factory()->count(1000)->create();
        Prospect::factory()->count(500)->create();
    }
}
```

### Exemples de Tests

```php
public function test_dashboard_load_time()
{
    // Arrange
    $start = microtime(true);

    // Act
    $response = $this->get('/admin/dashboard');

    // Assert
    $loadTime = microtime(true) - $start;
    $this->assertLessThan(2.0, $loadTime);
}

public function test_widget_refresh_performance()
{
    // Arrange
    $widget = new FinancialMetricsWidget();
    
    // Act
    $start = microtime(true);
    $widget->getStats();
    $refreshTime = microtime(true) - $start;

    // Assert
    $this->assertLessThan(0.5, $refreshTime);
}
```

## Commandes de Test

```bash
# Tests unitaires
php artisan test --filter=FinancialMetricsWidgetTest

# Tests d'intégration
php artisan test --filter=SuperAdminDashboardTest

# Tests visuels
php artisan dusk --filter=SuperAdminDashboardTest

# Tous les tests
php artisan test && php artisan dusk
```
