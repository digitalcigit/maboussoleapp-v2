<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::get('/admin/dashboard', function () {
        return view('admin.dashboard');
    })->name('filament.admin.pages.super-admin-dashboard');

    // Ajoutez d'autres routes administratives ici
});
