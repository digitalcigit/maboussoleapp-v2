<?php

namespace App\Providers;

use App\Database\Types\EnumType;
use Doctrine\DBAL\Types\Type;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Set error reporting level from config
        error_reporting(config('app.error_reporting', E_ALL & ~E_DEPRECATED));

        // Enregistrer le type ENUM avec Doctrine DBAL
        if (! Type::hasType('enum')) {
            Type::addType('enum', EnumType::class);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);

        // Enregistrer le mapping de type ENUM pour MySQL
        $connection = config('database.default');
        $driver = config("database.connections.{$connection}.driver");

        if ($driver === 'mysql') {
            $platform = \DB::connection()->getDoctrineSchemaManager()->getDatabasePlatform();
            $platform->registerDoctrineTypeMapping('enum', 'string');
        }
    }
}
