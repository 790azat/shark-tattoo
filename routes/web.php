<?php

use App\Http\Controllers\MediaController;
use App\Http\Controllers\SetupController;
use App\Http\Middleware\AdminOnly;
use App\Livewire;
use Illuminate\Support\Facades\Route;

Route::livewire('/', Livewire\Home::class)->name('home');
Route::livewire('/{lang}', Livewire\Home::class)->whereIn('lang', ['ru', 'en'])->name('home.lang');

Route::livewire('/admin/login', Livewire\Admin\Login::class)->name('admin.login');
Route::livewire('/admin', Livewire\Admin\Dashboard::class)->name('admin')
    ->middleware(AdminOnly::class);

Route::get('/media/{media}.webp', MediaController::class)->whereNumber('media')->name('media');
Route::get('/setup/{token}', SetupController::class)->name('setup');
