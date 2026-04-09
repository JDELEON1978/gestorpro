<?php

namespace App\Http\Controllers;

use App\Models\Catalogo;
use App\Models\UbicacionActivo;
use App\Models\Workspace;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UbicacionActivoController extends Controller
{
    public function index(Request $request, Workspace $workspace): View
    {
        $this->ensureWorkspaceAccess($request, $workspace);

        $query = $workspace->ubicacionesActivos()
            ->with(['central', 'parent'])
            ->withCount('activos')
            ->orderBy('nombre');

        if ($search = trim((string) $request->query('q', ''))) {
            $query->where(function ($inner) use ($search) {
                $inner->where('nombre', 'like', "%{$search}%")
                    ->orWhere('codigo', 'like', "%{$search}%");
            });
        }

        if ($centralId = (int) $request->query('central_id', 0)) {
            $query->where('central_id', $centralId);
        }

        if ($tipo = $request->query('tipo_ubicacion')) {
            $query->where('tipo_ubicacion', $tipo);
        }

        $ubicaciones = $query->paginate(15)->withQueryString();

        return view('ubicaciones.index', [
            'workspace' => $workspace,
            'ubicaciones' => $ubicaciones,
            'centrales' => $workspace->centralesGeneracion()->orderBy('nombre')->get(),
            'tipoOptions' => $this->catalogItems('tipo_ubicacion_activo'),
            'filters' => [
                'q' => $request->query('q'),
                'central_id' => $request->query('central_id'),
                'tipo_ubicacion' => $request->query('tipo_ubicacion'),
            ],
        ]);
    }

    public function create(Request $request, Workspace $workspace): View
    {
        $this->ensureWorkspaceAccess($request, $workspace);

        return view('ubicaciones.create', [
            'workspace' => $workspace,
            'ubicacion' => new UbicacionActivo(['activo' => true, 'tipo_ubicacion' => 'AREA']),
            ...$this->formOptions($workspace),
        ]);
    }

    public function store(Request $request, Workspace $workspace): RedirectResponse
    {
        $this->ensureWorkspaceAccess($request, $workspace);

        $data = $this->validatedData($request, $workspace);
        $data['workspace_id'] = $workspace->id;
        $data['activo'] = $request->boolean('activo', true);

        $ubicacion = UbicacionActivo::create($data);

        return redirect()
            ->route('workspaces.ubicaciones.show', [$workspace, $ubicacion])
            ->with('success', 'Ubicación creada correctamente.');
    }

    public function show(Request $request, Workspace $workspace, UbicacionActivo $ubicacion): View
    {
        $this->ensureWorkspaceAccess($request, $workspace);
        $this->ensureBelongsToWorkspace($workspace, $ubicacion);

        $ubicacion->load([
            'central',
            'parent',
            'children' => fn ($query) => $query->orderBy('nombre'),
            'activos' => fn ($query) => $query->orderBy('nombre')->limit(25),
        ]);

        return view('ubicaciones.show', compact('workspace', 'ubicacion'));
    }

    public function edit(Request $request, Workspace $workspace, UbicacionActivo $ubicacion): View
    {
        $this->ensureWorkspaceAccess($request, $workspace);
        $this->ensureBelongsToWorkspace($workspace, $ubicacion);

        return view('ubicaciones.edit', [
            'workspace' => $workspace,
            'ubicacion' => $ubicacion,
            ...$this->formOptions($workspace, $ubicacion),
        ]);
    }

    public function update(Request $request, Workspace $workspace, UbicacionActivo $ubicacion): RedirectResponse
    {
        $this->ensureWorkspaceAccess($request, $workspace);
        $this->ensureBelongsToWorkspace($workspace, $ubicacion);

        $data = $this->validatedData($request, $workspace, $ubicacion);
        $data['activo'] = $request->boolean('activo', false);

        $ubicacion->update($data);

        return redirect()
            ->route('workspaces.ubicaciones.show', [$workspace, $ubicacion])
            ->with('success', 'Ubicación actualizada correctamente.');
    }

    public function destroy(Request $request, Workspace $workspace, UbicacionActivo $ubicacion): RedirectResponse
    {
        $this->ensureWorkspaceAccess($request, $workspace);
        $this->ensureBelongsToWorkspace($workspace, $ubicacion);

        $ubicacion->delete();

        return redirect()
            ->route('workspaces.ubicaciones.index', $workspace)
            ->with('success', 'Ubicación eliminada correctamente.');
    }

    protected function validatedData(Request $request, Workspace $workspace, ?UbicacionActivo $ubicacion = null): array
    {
        return $request->validate([
            'central_id' => [
                'required',
                'integer',
                Rule::exists('centrales_generacion', 'id')->where(
                    fn ($query) => $query->where('workspace_id', $workspace->id)
                ),
            ],
            'parent_id' => [
                'nullable',
                'integer',
                Rule::exists('ubicaciones_activos', 'id')->where(
                    fn ($query) => $query->where('workspace_id', $workspace->id)
                ),
            ],
            'codigo' => [
                'required',
                'string',
                'max:50',
                Rule::unique('ubicaciones_activos', 'codigo')
                    ->where(fn ($query) => $query->where('central_id', $request->input('central_id')))
                    ->ignore($ubicacion?->id),
            ],
            'nombre' => ['required', 'string', 'max:255'],
            'tipo_ubicacion' => ['required', 'string', 'max:30'],
            'descripcion' => ['nullable', 'string'],
            'activo' => ['nullable', 'boolean'],
        ]);
    }

    protected function formOptions(Workspace $workspace, ?UbicacionActivo $ubicacion = null): array
    {
        return [
            'centrales' => $workspace->centralesGeneracion()->orderBy('nombre')->get(),
            'ubicacionesPadre' => $workspace->ubicacionesActivos()
                ->when($ubicacion, fn ($query) => $query->whereKeyNot($ubicacion->id))
                ->orderBy('nombre')
                ->get(),
            'tipoOptions' => $this->catalogItems('tipo_ubicacion_activo'),
        ];
    }

    protected function catalogItems(string $nombre): Collection
    {
        $items = Catalogo::query()
            ->where('nombre', $nombre)
            ->with(['items' => fn ($query) => $query->where('activo', true)->orderBy('valor')])
            ->first()
            ?->items
            ?? collect();

        if ($items->isNotEmpty()) {
            return $items;
        }

        return collect([
            (object) ['codigo' => 'PLANTA', 'valor' => 'Planta'],
            (object) ['codigo' => 'AREA', 'valor' => 'Área'],
            (object) ['codigo' => 'SISTEMA', 'valor' => 'Sistema'],
            (object) ['codigo' => 'SUBSISTEMA', 'valor' => 'Subsistema'],
            (object) ['codigo' => 'EDIFICIO', 'valor' => 'Edificio'],
            (object) ['codigo' => 'NIVEL', 'valor' => 'Nivel'],
            (object) ['codigo' => 'POSICION', 'valor' => 'Posición'],
        ]);
    }

    protected function ensureWorkspaceAccess(Request $request, Workspace $workspace): void
    {
        $allowed = $request->user()
            ->workspaces()
            ->where('workspaces.id', $workspace->id)
            ->exists();

        abort_unless($allowed, 403);
    }

    protected function ensureBelongsToWorkspace(Workspace $workspace, UbicacionActivo $ubicacion): void
    {
        abort_unless($ubicacion->workspace_id === $workspace->id, 404);
    }
}
