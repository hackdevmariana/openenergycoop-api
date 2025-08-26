<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    return view('welcome');
});

// Ruta personalizada para logout que maneja tanto GET como POST
Route::match(['GET', 'POST'], '/admin/logout', function () {
    Auth::logout();
    session()->invalidate();
    session()->regenerateToken();
    
    return redirect('/admin/login');
})->name('admin.logout');

// ========================================
// RUTAS PARA MÉTRICAS DE IMPACTO
// ========================================
Route::prefix('impact-metrics')->name('impact-metrics.')->group(function () {
    Route::get('/', [App\Http\Controllers\ImpactMetricsController::class, 'index'])->name('index');
    Route::get('/create', [App\Http\Controllers\ImpactMetricsController::class, 'create'])->name('create');
    Route::post('/', [App\Http\Controllers\ImpactMetricsController::class, 'store'])->name('store');
    Route::get('/{impactMetric}', [App\Http\Controllers\ImpactMetricsController::class, 'show'])->name('show');
    Route::get('/{impactMetric}/edit', [App\Http\Controllers\ImpactMetricsController::class, 'edit'])->name('edit');
    Route::put('/{impactMetric}', [App\Http\Controllers\ImpactMetricsController::class, 'update'])->name('update');
    Route::delete('/{impactMetric}', [App\Http\Controllers\ImpactMetricsController::class, 'destroy'])->name('destroy');
    
    // Rutas especializadas
    Route::get('/by-user/{userId}', [App\Http\Controllers\ImpactMetricsController::class, 'byUser'])->name('by-user');
    Route::get('/by-plant-group/{plantGroupId}', [App\Http\Controllers\ImpactMetricsController::class, 'byPlantGroup'])->name('by-plant-group');
    Route::get('/global', [App\Http\Controllers\ImpactMetricsController::class, 'global'])->name('global');
    Route::get('/individual', [App\Http\Controllers\ImpactMetricsController::class, 'individual'])->name('individual');
    Route::get('/recent', [App\Http\Controllers\ImpactMetricsController::class, 'recent'])->name('recent');
    Route::get('/this-month', [App\Http\Controllers\ImpactMetricsController::class, 'thisMonth'])->name('this-month');
    Route::get('/this-year', [App\Http\Controllers\ImpactMetricsController::class, 'thisYear'])->name('this-year');
    Route::get('/statistics', [App\Http\Controllers\ImpactMetricsController::class, 'statistics'])->name('statistics');
    Route::post('/{impactMetric}/update-metrics', [App\Http\Controllers\ImpactMetricsController::class, 'updateMetrics'])->name('update-metrics');
    Route::post('/{impactMetric}/reset-metrics', [App\Http\Controllers\ImpactMetricsController::class, 'resetMetrics'])->name('reset-metrics');
});

// ========================================
// RUTAS PARA MÉTRICAS COMUNITARIAS
// ========================================
Route::prefix('community-metrics')->name('community-metrics.')->group(function () {
    Route::get('/', [App\Http\Controllers\CommunityMetricsController::class, 'index'])->name('index');
    Route::get('/create', [App\Http\Controllers\CommunityMetricsController::class, 'create'])->name('create');
    Route::post('/', [App\Http\Controllers\CommunityMetricsController::class, 'store'])->name('store');
    Route::get('/{communityMetric}', [App\Http\Controllers\CommunityMetricsController::class, 'show'])->name('show');
    Route::get('/{communityMetric}/edit', [App\Http\Controllers\CommunityMetricsController::class, 'edit'])->name('edit');
    Route::put('/{communityMetric}', [App\Http\Controllers\CommunityMetricsController::class, 'update'])->name('update');
    Route::delete('/{communityMetric}', [App\Http\Controllers\CommunityMetricsController::class, 'destroy'])->name('destroy');
    
    // Rutas especializadas
    Route::get('/by-organization/{organizationId}', [App\Http\Controllers\CommunityMetricsController::class, 'byOrganization'])->name('by-organization');
    Route::get('/active', [App\Http\Controllers\CommunityMetricsController::class, 'active'])->name('active');
    Route::get('/inactive', [App\Http\Controllers\CommunityMetricsController::class, 'inactive'])->name('inactive');
    Route::get('/recent', [App\Http\Controllers\CommunityMetricsController::class, 'recent'])->name('recent');
    Route::get('/this-month', [App\Http\Controllers\CommunityMetricsController::class, 'thisMonth'])->name('this-month');
    Route::get('/this-year', [App\Http\Controllers\CommunityMetricsController::class, 'thisYear'])->name('this-year');
    Route::get('/statistics', [App\Http\Controllers\CommunityMetricsController::class, 'statistics'])->name('statistics');
    Route::post('/{communityMetric}/add-user', [App\Http\Controllers\CommunityMetricsController::class, 'addUser'])->name('add-user');
    Route::post('/{communityMetric}/remove-user', [App\Http\Controllers\CommunityMetricsController::class, 'removeUser'])->name('remove-user');
    Route::post('/{communityMetric}/add-kwh-production', [App\Http\Controllers\CommunityMetricsController::class, 'addKwhProduction'])->name('add-kwh-production');
    Route::post('/{communityMetric}/add-co2-avoided', [App\Http\Controllers\CommunityMetricsController::class, 'addCo2Avoided'])->name('add-co2-avoided');
    Route::post('/{communityMetric}/reset-metrics', [App\Http\Controllers\CommunityMetricsController::class, 'resetMetrics'])->name('reset-metrics');
});

// Ruta temporal para probar iconos de Bootstrap
Route::get('/test-icons', function () {
    return view('test-bootstrap-icons');
})->name('test.icons');
