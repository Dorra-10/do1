<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\DocumentController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Routes accessibles uniquement aux utilisateurs authentifiés
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/documents', [DocumentController::class, 'index'])->name('documents.index');
    Route::resource('documents', DocumentController::class);
    Route::resource('documents', DocumentController::class)->except(['index', 'store']);
    Route::get('/documents/{document}/edit', [DocumentController::class, 'edit'])->name('documents.edit');

    
     // Pour stocker un projet
    Route::post('/documents/store', [DocumentController::class, 'store'])->name('documents.store');
    Route::get('projects/{project}/documents', [ProjectController::class, 'showDocuments'])->name('projects.documents');


});

// Routes accessibles uniquement au admin
Route::group(['middleware' => ['role:admin']], function () {
    // Permissions
    Route::resource('permissions', PermissionController::class);
    Route::delete('permissions/{permissionId}/delete', [PermissionController::class, 'destroy'])->name('permissions.destroy');

    // Roles
    Route::resource('roles', RoleController::class);
    Route::delete('roles/{role}/delete', [RoleController::class, 'destroy'])->name('roles.destroy');

    Route::put('roles/{roleId}/give-permissions', [RoleController::class, 'addPermissionToRole'])->name('roles.addPermission');
    
    // Users
   
     // Définir les autres routes des projets
    Route::resource('projects', ProjectController::class);
    Route::resource('projects', ProjectController::class)->except(['index', 'store']);
    
     // Pour stocker un projet
    Route::post('/projects/store', [ProjectController::class, 'store'])->name('projects.store');
    Route::put('/projects/{id}', [ProjectController::class, 'update'])->name('projects.update');
    Route::delete('/projects/{id}', [ProjectController::class, 'destroy'])->name('projects.destroy');
    //documents
    Route::resource('documents', DocumentController::class)->except(['index', 'store']);
    Route::get('/documents/{document}/edit', [DocumentController::class, 'edit'])->name('documents.edit');
    Route::put('documents/{document}', [DocumentController::class, 'update'])->name('documents.update');  // Mettre à jour un document
    Route::delete('documents/{document}', [DocumentController::class, 'destroy'])->name('documents.destroy');
  // Supprimer un document
    
});

// Routes accessibles aux admin, superviseur et employé
Route::group(['middleware' => ['auth', 'role:admin|superviseur|employee']], function () {
    // Route pour voir les projets
    Route::match(['get', 'post'], '/projects', [ProjectController::class, 'index'])->name('projects.index');
    Route::resource('users', UserController::class)->except(['show']);
    Route::match(['get', 'post'], '/users', [UserController::class, 'index'])->name('role-permission.user.index');
    Route::match(['get', 'post'], '/projects', [ProjectController::class, 'index'])->name('projects.index');
   
});
Route::group(['middleware' => ['role:admin|superviseur']], function () {
    Route::post('/projects/store', [ProjectController::class, 'store'])->name('projects.store');
    Route::put('/projects/{id}', [ProjectController::class, 'update'])->name('projects.update');
    Route::delete('/projects/{id}', [ProjectController::class, 'destroy'])->name('projects.destroy');
    Route::resource('users', UserController::class)->except(['show']);

});
   

require __DIR__.'/auth.php';
