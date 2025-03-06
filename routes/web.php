<?php

use App\Http\Controllers\SystemInitializationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    if (auth()->check()) {
        if (auth()->user()->hasRole('portail_candidat')) {
            return redirect('/portail-candidat');
        }
        return redirect('/admin');
    }
    return redirect('/portail-candidat/login');
});

Route::get('/system/initialization', [SystemInitializationController::class, 'showInitializationForm'])
    ->name('system.initialization');

Route::post('/system/initialize', [SystemInitializationController::class, 'initialize'])
    ->name('system.initialize');

Route::get('/documents/{document}/preview', \App\Http\Controllers\DossierDocumentPreviewController::class)
    ->name('dossier-documents.preview')
    ->middleware(['auth']);
