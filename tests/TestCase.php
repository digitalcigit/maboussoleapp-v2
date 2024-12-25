<?php

namespace Tests;

use App\Models\User;
use Filament\FilamentServiceProvider;
use Filament\Forms\FormsServiceProvider;
use Filament\Support\SupportServiceProvider;
use Filament\Tables\TablesServiceProvider;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Livewire\LivewireServiceProvider;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create and authenticate a user for each test
        $user = User::factory()->create();
        $this->actingAs($user);
    }

    protected function getPackageProviders($app)
    {
        return [
            LivewireServiceProvider::class,
            FilamentServiceProvider::class,
            FormsServiceProvider::class,
            TablesServiceProvider::class,
            SupportServiceProvider::class,
        ];
    }
}
