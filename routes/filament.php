<?php

use App\Filament\Pages\SystemInitialization;
use Illuminate\Support\Facades\Route;

Route::domain(config('app.url'))
    ->middleware([
        'web',
        'filament.init',
    ])
    ->group(function () {
        Route::get('admin/system-initialization', SystemInitialization::class)->name('filament.admin.system-initialization');
    });
