<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\WorkspaceController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\ProjectStatusController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
| Aquí definimos rutas HTTP para la aplicación web.
| Regla: todo lo que sea "interno" debe ir protegido por middleware auth.
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    // Landing simple: si ya existe dashboard, redirigimos.
    return redirect()->route('dashboard');
});

/*
|--------------------------------------------------------------------------
| Rutas protegidas (requieren login)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {

    // Dashboard principal
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    /*
    |--------------------------------------------------------------------------
    | Workspaces (MVP)
    |--------------------------------------------------------------------------
    */
    Route::get('/workspaces', [WorkspaceController::class, 'index'])
        ->name('workspaces.index');

    Route::get('/workspaces/create', [WorkspaceController::class, 'create'])
        ->name('workspaces.create');

    Route::post('/workspaces', [WorkspaceController::class, 'store'])
        ->name('workspaces.store');



        /*
        |--------------------------------------------------------------------------
        | Tasks (Kanban)
        |--------------------------------------------------------------------------
        | index/create/store ya existen
        | move: mover/reordenar tareas en el kanban
        */
        Route::get('/projects/{project}/tasks', [TaskController::class, 'index'])
            ->name('projects.tasks.index');

        Route::get('/projects/{project}/tasks/create', [TaskController::class, 'create'])
            ->name('projects.tasks.create');

        Route::post('/projects/{project}/tasks', [TaskController::class, 'store'])
            ->name('projects.tasks.store');

        // ✅ Movimiento Kanban (drag & drop)
        Route::patch('/tasks/{task}/move', [TaskController::class, 'move'])
            ->name('tasks.move');
        Route::post('/projects/{project}/statuses', [ProjectStatusController::class, 'store'])
            ->name('projects.statuses.store');


    /*
    |--------------------------------------------------------------------------
    | Projects (dentro de un workspace)
    |--------------------------------------------------------------------------
    | Seguridad multi-tenant:
    | El acceso real se controla dentro del ProjectController con ProjectPolicy.
    */
    Route::get('/workspaces/{workspace}/projects', [ProjectController::class, 'index'])
        ->name('workspaces.projects.index');

    Route::get('/workspaces/{workspace}/projects/create', [ProjectController::class, 'create'])
        ->name('workspaces.projects.create');

    Route::post('/workspaces/{workspace}/projects', [ProjectController::class, 'store'])
        ->name('workspaces.projects.store');
});

/*
|--------------------------------------------------------------------------
| Rutas de autenticación (Breeze/Fortify/etc.)
|--------------------------------------------------------------------------
| Se dejan al final para mantener orden y evitar confusiones al depurar.
*/
require __DIR__ . '/auth.php';
