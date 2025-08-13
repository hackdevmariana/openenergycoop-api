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
