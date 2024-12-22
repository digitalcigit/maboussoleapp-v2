<?php

use Illuminate\Support\Facades\Route;
use App\Filament\Pages\SystemInitialization;

Route::domain(config('app.url'))
    ->middleware([
        'web',
        'filament.init',
    ])
    ->group(function () {
        Route::get('admin/system-initialization', SystemInitialization::class)->name('filament.admin.system-initialization');
    });
