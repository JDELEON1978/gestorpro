<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\WorkspaceController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\ProjectStatusController;
use App\Http\Controllers\TaskFileController;
use App\Http\Controllers\ProcessBuilderController;
use App\Http\Controllers\ExpedienteController;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

/*
|--------------------------------------------------------------------------
| Rutas protegidas (requieren login)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    /*
    |--------------------------------------------------------------------------
    | Workspaces
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
    | Process Builder
    |--------------------------------------------------------------------------
    */

    // Vista principal (selector de proceso + canvas)
    Route::get('/process-builder/{proceso?}', [ProcessBuilderController::class, 'index'])
        ->name('process.builder');

    // Proceso
    Route::post('/process-builder/proceso', [ProcessBuilderController::class, 'storeProceso'])
        ->name('builder.proceso.store');

    Route::put('/process-builder/proceso/{proceso}', [ProcessBuilderController::class, 'updateProceso'])
        ->name('builder.proceso.update');

    // Nodos
    Route::post('/process-builder/{proceso}/nodo', [ProcessBuilderController::class, 'storeNodo'])
        ->name('builder.nodo.store');

    Route::put('/process-builder/nodo/{nodo}', [ProcessBuilderController::class, 'updateNodo'])
        ->name('builder.nodo.update');

    // Drag position
    Route::patch('/process-builder/nodo/{nodo}/position', [ProcessBuilderController::class, 'updateNodoPosition'])
        ->name('builder.nodo.position');

    Route::patch('/process-builder/nodo/{nodo}/port', [ProcessBuilderController::class, 'updateNodoPort'])->name('builder.nodo.port');


    // Grafo
    Route::get('/process-builder/{proceso}/graph', [ProcessBuilderController::class, 'graph'])
        ->name('builder.graph');

    // Relación simple (click verde -> click azul)
    Route::post('/process-builder/{proceso}/relacion', [ProcessBuilderController::class, 'storeRelacion'])
        ->name('builder.relacion.store');
    Route::patch('/process-builder/relacion/{relacion}/port', [ProcessBuilderController::class, 'updateRelacionPort'])
        ->name('builder.relacion.port');
    Route::patch('/process-builder/relacion/{relacion}/bend', [ProcessBuilderController::class, 'updateRelacionBend'])
        ->name('builder.relacion.bend');


    // Relación avanzada por nodo (modal: transiciones)
    Route::get('/process-builder/nodo/{nodo}/relaciones', [ProcessBuilderController::class, 'relacionesNodo'])
        ->name('builder.nodo.relaciones.index');

    Route::post('/process-builder/nodo/{nodo}/relaciones', [ProcessBuilderController::class, 'guardarRelacionesNodo'])
        ->name('builder.nodo.relaciones.store');

    // Items
    Route::post('/process-builder/{proceso}/item', [ProcessBuilderController::class, 'storeItem'])
        ->name('builder.item.store');

    Route::put('/process-builder/item/{item}', [ProcessBuilderController::class, 'updateItem'])
        ->name('builder.item.update');

    /*
    |--------------------------------------------------------------------------
    | Expedientes
    |--------------------------------------------------------------------------
    */
    Route::get('/expedientes', [ExpedienteController::class, 'index'])
        ->name('expedientes.index');

    Route::post('/expedientes', [ExpedienteController::class, 'store'])
        ->name('expedientes.store');

    Route::get('/expedientes/{expediente}', [ExpedienteController::class, 'show'])
        ->name('expedientes.show');

    Route::post('/expedientes/{expediente}/transition', [ExpedienteController::class, 'transition'])
        ->name('expedientes.transition');

    Route::get('/expedientes/{expediente}/can-transition', [ExpedienteController::class, 'canTransition'])
        ->name('expedientes.can_transition');

    Route::post('/expediente-items/{expedienteItem}/evidencias', [ExpedienteController::class, 'uploadEvidencia'])
        ->name('expediente_items.evidencias.store');

    Route::post('/expediente-items/{expedienteItem}/review', [ExpedienteController::class, 'reviewItem'])
        ->name('expediente_items.review');

    /*
    |--------------------------------------------------------------------------
    | Projects / Tasks
    |--------------------------------------------------------------------------
    */
    Route::get('/workspaces/{workspace}/projects', [ProjectController::class, 'index'])
        ->name('workspaces.projects.index');

    Route::get('/workspaces/{workspace}/projects/create', [ProjectController::class, 'create'])
        ->name('workspaces.projects.create');

    Route::post('/workspaces/{workspace}/projects', [ProjectController::class, 'store'])
        ->name('workspaces.projects.store');

    Route::get('/projects/{project}/tasks', [TaskController::class, 'index'])
        ->name('projects.tasks.index');

    Route::get('/projects/{project}/tasks/create', [TaskController::class, 'create'])
        ->name('projects.tasks.create');

    Route::post('/projects/{project}/tasks', [TaskController::class, 'store'])
        ->name('projects.tasks.store');

    Route::patch('/tasks/{task}/move', [TaskController::class, 'move'])
        ->name('tasks.move');

    Route::patch('/tasks/{task}', [TaskController::class, 'update'])
        ->name('tasks.update');

    Route::post('/projects/{project}/statuses', [ProjectStatusController::class, 'store'])
        ->name('projects.statuses.store');

    // Task files
    Route::get('/tasks/{task}/files', [TaskFileController::class, 'index'])
        ->name('tasks.files.index');

    Route::post('/tasks/{task}/files', [TaskFileController::class, 'store'])
        ->name('tasks.files.store');

    Route::get('/tasks/{task}/files/{file}/download', [TaskFileController::class, 'download'])
        ->name('tasks.files.download');

    Route::delete('/tasks/{task}/files/{file}', [TaskFileController::class, 'destroy'])
        ->name('tasks.files.destroy');
});

require __DIR__ . '/auth.php';