<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\WorkspaceController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\ProjectStatusController;
use App\Http\Controllers\TaskFileController;
use App\Http\Controllers\ProcessBuilderController;


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

        
        Route::get('/process-builder/{proceso?}', [ProcessBuilderController::class, 'index'])
            ->name('process.builder');

        // ITEMS
        Route::post('/process-builder/{proceso}/item', [ProcessBuilderController::class, 'storeItem'])
            ->name('builder.item.store');

        Route::put('/process-builder/item/{item}', [ProcessBuilderController::class, 'updateItem'])
            ->name('builder.item.update');
        
        Route::get('/process-builder/{proceso?}', [ProcessBuilderController::class, 'index'])
            ->name('process.builder');

        Route::get('/process-builder/{proceso?}', [ProcessBuilderController::class, 'index'])
        ->name('process.builder');

        // NODOS
        Route::post('/process-builder/{proceso}/nodo', [ProcessBuilderController::class, 'storeNodo'])
            ->name('builder.nodo.store');

        Route::put('/process-builder/nodo/{nodo}', [ProcessBuilderController::class, 'updateNodo'])
            ->name('builder.nodo.update');

        // POSICIÓN NODO (drag)
        Route::patch('/process-builder/nodo/{nodo}/position', [ProcessBuilderController::class, 'updateNodoPosition'])
            ->name('builder.nodo.position');

        // GRAFO (nodos + relaciones)
        Route::get('/process-builder/{proceso}/graph', [ProcessBuilderController::class, 'graph'])
            ->name('builder.graph');

        // RELACIONES (nuevo)
        Route::post('/process-builder/{proceso}/relacion', [ProcessBuilderController::class, 'storeRelacion'])
            ->name('builder.relacion.store');


/*
|--------------------------------------------------------------------------
| Dashboard 2
|--------------------------------------------------------------------------
*/        

        Route::middleware(['auth'])->group(function () {
            Route::get('/process-builder/{proceso?}', [ProcessBuilderController::class, 'index'])->name('process.builder');

            // Proceso
            Route::post('/process-builder/proceso', [ProcessBuilderController::class, 'storeProceso'])->name('builder.proceso.store');
            Route::put('/process-builder/proceso/{proceso}', [ProcessBuilderController::class, 'updateProceso'])->name('builder.proceso.update');

            // Nodo
            Route::post('/process-builder/{proceso}/nodo', [ProcessBuilderController::class, 'storeNodo'])->name('builder.nodo.store');
            Route::put('/process-builder/nodo/{nodo}', [ProcessBuilderController::class, 'updateNodo'])->name('builder.nodo.update');

            // Item
            Route::post('/process-builder/{proceso}/item', [ProcessBuilderController::class, 'storeItem'])->name('builder.item.store');
            Route::put('/process-builder/item/{item}', [ProcessBuilderController::class, 'updateItem'])->name('builder.item.update');
        });




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
        | Task (dentro de un workspace)
        |--------------------------------------------------------------------------
        |  
        | 
        */

        Route::patch('/tasks/{task}', [TaskController::class, 'update'])
        ->name('tasks.update');

        Route::get('/tasks/{task}/files', [TaskFileController::class, 'index'])
             ->name('tasks.files.index');

        Route::post('/tasks/{task}/files', [TaskFileController::class, 'store'])
            ->name('tasks.files.store');

        Route::get('/tasks/{task}/files/{file}/download', [TaskFileController::class, 'download'])
            ->name('tasks.files.download');

        Route::delete('/tasks/{task}/files/{file}', [TaskFileController::class, 'destroy'])
            ->name('tasks.files.destroy');

/*
|--------------------------------------------------------------------------
| Rutas de autenticación (Breeze/Fortify/etc.)
|--------------------------------------------------------------------------
| Se dejan al final para mantener orden y evitar confusiones al depurar.
*/
require __DIR__ . '/auth.php';
