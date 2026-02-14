<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\WorkspaceController;

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::post('/workspaces', [WorkspaceController::class, 'store'])->name('workspaces.store');
});
