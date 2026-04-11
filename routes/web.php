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
use App\Http\Controllers\UserRoleController;
use App\Http\Controllers\TaskEvidenceController;
use App\Http\Controllers\TaskActivityController;
use App\Http\Controllers\TaskAuditController;
use App\Http\Controllers\ActivoController;
use App\Http\Controllers\CentralGeneracionController;
use App\Http\Controllers\UbicacionActivoController;
use App\Http\Controllers\CategoriaActivoController;
use App\Http\Controllers\ActivoEventoController;
use App\Http\Controllers\ActivoContactoController;
use App\Http\Controllers\ActivoDocumentoController;
use App\Http\Controllers\ActivoEventoEvidenciaController;


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
    Route::get('/process-builder/item/{item}/examples', [\App\Http\Controllers\ItemExampleFileController::class, 'index']);
    Route::post('/process-builder/item/{item}/examples', [\App\Http\Controllers\ItemExampleFileController::class, 'store']);
    Route::get('/process-builder/item-examples/{file}/download', [\App\Http\Controllers\ItemExampleFileController::class, 'download']);
    Route::delete('/process-builder/item-examples/{file}', [\App\Http\Controllers\ItemExampleFileController::class, 'destroy']);

    // Vista principal (selector de proceso + canvas)
    Route::get('/process-builder/{proceso?}', [ProcessBuilderController::class, 'index'])
        ->name('process.builder');
    Route::get('/process-builder/{proceso}/print', [ProcessBuilderController::class, 'print'])
        ->name('process.builder.print');

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

    Route::get('/process-builder/nodo/{nodo}/items', [ProcessBuilderController::class, 'itemsNodo'])
    ->name('builder.nodo.items.index');

    // Grafo
    Route::get('/process-builder/{proceso}/graph', [ProcessBuilderController::class, 'graph'])
        ->name('builder.graph');
    Route::post('/process-builder/{proceso}/auto-layout', [ProcessBuilderController::class, 'autoLayout'])
        ->name('builder.auto_layout');

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
    Route::get('/expedientes', [ExpedienteController::class, 'index'])->name('expedientes.index');

    Route::post('/expedientes', [ExpedienteController::class, 'store'])->name('expedientes.store');

    Route::get('/expedientes/{expediente}', [ExpedienteController::class, 'show'])->name('expedientes.show');

    Route::post('/expedientes/{expediente}/transition', [ExpedienteController::class, 'transition'])->name('expedientes.transition');

    Route::get('/expedientes/{expediente}/can-transition', [ExpedienteController::class, 'canTransition'])->name('expedientes.can_transition');

    Route::post('/expediente-items/{expedienteItem}/evidencias', [ExpedienteController::class, 'uploadEvidencia'])->name('expediente_items.evidencias.store');

    Route::post('/expediente-items/{expedienteItem}/review', [ExpedienteController::class, 'reviewItem'])->name('expediente_items.review');

    /*
    |--------------------------------------------------------------------------
    | Projects / Tasks
    |--------------------------------------------------------------------------
    */
    Route::get('/workspaces/{workspace}/projects', [ProjectController::class, 'index'])->name('workspaces.projects.index');
    Route::get('/workspaces/{workspace}/projects/create', [ProjectController::class, 'create'])->name('workspaces.projects.create');
    Route::post('/workspaces/{workspace}/projects', [ProjectController::class, 'store'])->name('workspaces.projects.store');
    Route::get('/workspaces/{workspace}/centrales', [CentralGeneracionController::class, 'index'])->name('workspaces.centrales.index');
    Route::get('/workspaces/{workspace}/centrales/create', [CentralGeneracionController::class, 'create'])->name('workspaces.centrales.create');
    Route::post('/workspaces/{workspace}/centrales', [CentralGeneracionController::class, 'store'])->name('workspaces.centrales.store');
    Route::get('/workspaces/{workspace}/centrales/{central}', [CentralGeneracionController::class, 'show'])->name('workspaces.centrales.show');
    Route::get('/workspaces/{workspace}/centrales/{central}/edit', [CentralGeneracionController::class, 'edit'])->name('workspaces.centrales.edit');
    Route::put('/workspaces/{workspace}/centrales/{central}', [CentralGeneracionController::class, 'update'])->name('workspaces.centrales.update');
    Route::delete('/workspaces/{workspace}/centrales/{central}', [CentralGeneracionController::class, 'destroy'])->name('workspaces.centrales.destroy');
    Route::get('/workspaces/{workspace}/ubicaciones', [UbicacionActivoController::class, 'index'])->name('workspaces.ubicaciones.index');
    Route::get('/workspaces/{workspace}/ubicaciones/create', [UbicacionActivoController::class, 'create'])->name('workspaces.ubicaciones.create');
    Route::post('/workspaces/{workspace}/ubicaciones', [UbicacionActivoController::class, 'store'])->name('workspaces.ubicaciones.store');
    Route::get('/workspaces/{workspace}/ubicaciones/{ubicacion}', [UbicacionActivoController::class, 'show'])->name('workspaces.ubicaciones.show');
    Route::get('/workspaces/{workspace}/ubicaciones/{ubicacion}/edit', [UbicacionActivoController::class, 'edit'])->name('workspaces.ubicaciones.edit');
    Route::put('/workspaces/{workspace}/ubicaciones/{ubicacion}', [UbicacionActivoController::class, 'update'])->name('workspaces.ubicaciones.update');
    Route::delete('/workspaces/{workspace}/ubicaciones/{ubicacion}', [UbicacionActivoController::class, 'destroy'])->name('workspaces.ubicaciones.destroy');
    Route::get('/workspaces/{workspace}/categorias', [CategoriaActivoController::class, 'index'])->name('workspaces.categorias.index');
    Route::get('/workspaces/{workspace}/categorias/create', [CategoriaActivoController::class, 'create'])->name('workspaces.categorias.create');
    Route::post('/workspaces/{workspace}/categorias', [CategoriaActivoController::class, 'store'])->name('workspaces.categorias.store');
    Route::get('/workspaces/{workspace}/categorias/{categoria}', [CategoriaActivoController::class, 'show'])->name('workspaces.categorias.show');
    Route::get('/workspaces/{workspace}/categorias/{categoria}/edit', [CategoriaActivoController::class, 'edit'])->name('workspaces.categorias.edit');
    Route::put('/workspaces/{workspace}/categorias/{categoria}', [CategoriaActivoController::class, 'update'])->name('workspaces.categorias.update');
    Route::delete('/workspaces/{workspace}/categorias/{categoria}', [CategoriaActivoController::class, 'destroy'])->name('workspaces.categorias.destroy');
    Route::get('/workspaces/{workspace}/activos', [ActivoController::class, 'index'])->name('workspaces.activos.index');
    Route::get('/workspaces/{workspace}/activos/create', [ActivoController::class, 'create'])->name('workspaces.activos.create');
    Route::post('/workspaces/{workspace}/activos', [ActivoController::class, 'store'])->name('workspaces.activos.store');
    Route::get('/workspaces/{workspace}/activos/{activo}', [ActivoController::class, 'show'])->name('workspaces.activos.show');
    Route::get('/workspaces/{workspace}/activos/{activo}/edit', [ActivoController::class, 'edit'])->name('workspaces.activos.edit');
    Route::put('/workspaces/{workspace}/activos/{activo}', [ActivoController::class, 'update'])->name('workspaces.activos.update');
    Route::delete('/workspaces/{workspace}/activos/{activo}', [ActivoController::class, 'destroy'])->name('workspaces.activos.destroy');
    Route::post('/workspaces/{workspace}/activos/{activo}/contactos', [ActivoContactoController::class, 'store'])->name('workspaces.activos.contactos.store');
    Route::get('/workspaces/{workspace}/activos/{activo}/contactos/{contacto}/edit', [ActivoContactoController::class, 'edit'])->name('workspaces.activos.contactos.edit');
    Route::put('/workspaces/{workspace}/activos/{activo}/contactos/{contacto}', [ActivoContactoController::class, 'update'])->name('workspaces.activos.contactos.update');
    Route::delete('/workspaces/{workspace}/activos/{activo}/contactos/{contacto}', [ActivoContactoController::class, 'destroy'])->name('workspaces.activos.contactos.destroy');
    Route::post('/workspaces/{workspace}/activos/{activo}/documentos', [ActivoDocumentoController::class, 'store'])->name('workspaces.activos.documentos.store');
    Route::get('/workspaces/{workspace}/activos/{activo}/documentos/{documento}/download', [ActivoDocumentoController::class, 'download'])->name('workspaces.activos.documentos.download');
    Route::delete('/workspaces/{workspace}/activos/{activo}/documentos/{documento}', [ActivoDocumentoController::class, 'destroy'])->name('workspaces.activos.documentos.destroy');
    Route::get('/workspaces/{workspace}/eventos', [ActivoEventoController::class, 'index'])->name('workspaces.eventos.index');
    Route::post('/workspaces/{workspace}/activos/{activo}/eventos', [ActivoEventoController::class, 'store'])->name('workspaces.activos.eventos.store');
    Route::get('/workspaces/{workspace}/activos/{activo}/eventos/{evento}/edit', [ActivoEventoController::class, 'edit'])->name('workspaces.activos.eventos.edit');
    Route::put('/workspaces/{workspace}/activos/{activo}/eventos/{evento}', [ActivoEventoController::class, 'update'])->name('workspaces.activos.eventos.update');
    Route::delete('/workspaces/{workspace}/activos/{activo}/eventos/{evento}', [ActivoEventoController::class, 'destroy'])->name('workspaces.activos.eventos.destroy');
    Route::post('/workspaces/{workspace}/activos/{activo}/eventos/{evento}/evidencias', [ActivoEventoEvidenciaController::class, 'store'])->name('workspaces.activos.eventos.evidencias.store');
    Route::get('/workspaces/{workspace}/activos/{activo}/eventos/{evento}/evidencias/{evidencia}/download', [ActivoEventoEvidenciaController::class, 'download'])->name('workspaces.activos.eventos.evidencias.download');
    Route::delete('/workspaces/{workspace}/activos/{activo}/eventos/{evento}/evidencias/{evidencia}', [ActivoEventoEvidenciaController::class, 'destroy'])->name('workspaces.activos.eventos.evidencias.destroy');
    Route::get('/projects/{project}/tasks', [TaskController::class, 'index'])->name('projects.tasks.index');
    Route::get('/projects/{project}/tasks/create', [TaskController::class, 'create'])->name('projects.tasks.create');
    Route::post('/projects/{project}/tasks', [TaskController::class, 'store'])->name('projects.tasks.store');
    Route::get('/projects/{project}/start-node', [ProjectController::class, 'startNode'])->name('projects.start_node');
    
    Route::get('/projects/{project}/start-tasks', [ProjectController::class, 'startTasks']);
    Route::get('/tasks/{task}/chain', [TaskController::class, 'chain'])->name('tasks.chain');

    Route::get('/tasks/{task}/activities', [TaskActivityController::class, 'index'])->name('tasks.activities');
    Route::get('/tasks/{task}/audit-review', [TaskAuditController::class, 'show'])->name('tasks.audit-review');
    
    Route::patch('/tasks/{task}/move', [TaskController::class, 'move'])->name('tasks.move');
    Route::patch('/tasks/{task}', [TaskController::class, 'update'])->name('tasks.update');
    Route::post('/projects/{project}/statuses', [ProjectStatusController::class, 'store'])->name('projects.statuses.store');
    // Task files
    Route::get('/tasks/{task}/files', [TaskFileController::class, 'index'])->name('tasks.files.index');
    Route::post('/tasks/{task}/files', [TaskFileController::class, 'store'])->name('tasks.files.store');
    Route::get('/tasks/{task}/files/{file}/download', [TaskFileController::class, 'download'])->name('tasks.files.download');
    Route::delete('/tasks/{task}/files/{file}', [TaskFileController::class, 'destroy'])->name('tasks.files.destroy');
    Route::post('/tasks/{task}/advance', [TaskController::class, 'advance'])->name('tasks.advance');
    Route::get('/tasks/{task}/evidences', [TaskEvidenceController::class, 'index']);
    Route::post('/tasks/{task}/evidences/{item}', [TaskEvidenceController::class, 'store']);
    

    /*
    |--------------------------------------------------------------------------
    | User
    |--------------------------------------------------------------------------
    */
    Route::get('/admin/user-roles', [UserRoleController::class, 'index'])
        ->name('admin.user_roles.index');
    Route::put('/admin/user-roles/{user}', [UserRoleController::class, 'update'])
        ->name('admin.user_roles.update');
    

});

require __DIR__ . '/auth.php';
