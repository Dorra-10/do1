<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProjectController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Routes accessibles uniquement aux utilisateurs authentifiés
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Routes accessibles uniquement au admin
Route::group(['middleware' => ['role:admin']], function () {
    // Permissions
    Route::resource('permissions', PermissionController::class);
    Route::delete('permissions/{permissionId}/delete', [PermissionController::class, 'destroy'])->name('permissions.destroy');

    // Roles
    Route::resource('roles', RoleController::class);
    Route::delete('roles/{role}/delete', [RoleController::class, 'destroy'])->name('roles.destroy');

    Route::get('roles/{roleId}/give-permissions', [RoleController::class, 'addPermissionToRole'])->name('roles.addPermission');
    
    // Users
    
     // Définir les autres routes des projets
    Route::resource('projects', ProjectController::class);
    Route::resource('projects', ProjectController::class)->except(['index', 'store']);
    
     // Pour stocker un projet
    Route::post('/projects/store', [ProjectController::class, 'store'])->name('projects.store');
    Route::put('/projects/{id}', [ProjectController::class, 'update'])->name('projects.update');
    Route::delete('/projects/{id}', [ProjectController::class, 'destroy'])->name('projects.destroy');
     
});

// Routes accessibles aux admin, superviseur et employé
Route::group(['middleware' => ['auth', 'role:admin|superviseur|employee']], function () {
    // Route pour voir les projets
    Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');
    Route::resource('users', UserController::class)->except(['show']);
   
});
Route::group(['middleware' => ['role:admin|superviseur']], function () {
    Route::post('/projects/store', [ProjectController::class, 'store'])->name('projects.store');
    Route::put('/projects/{id}', [ProjectController::class, 'update'])->name('projects.update');
    Route::delete('/projects/{id}', [ProjectController::class, 'destroy'])->name('projects.destroy');
    Route::resource('users', UserController::class)->except(['show']);

});
   

require __DIR__.'/auth.php';
