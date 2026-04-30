<?php

use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class,'index'])->name('home');

Route::middleware(['auth'])->group(function () {
    Route::livewire('dashboard', 'dashboard.index')->name('dashboard');
    Route::livewire('history', 'history')->name('history');
    Route::livewire('transaksi', 'transaksi')->name('transaksi');
});
Route::middleware(['auth', 'role:admin'])->group(function () {

    Route::livewire('kategori', 'kategori')->name('kategori');
    Route::livewire('produk', 'produk')->name('produk');
});

require __DIR__ . '/settings.php';
