<?php

namespace App\Http\Controllers;

use App\Models\Catalogo;
use App\Models\CentralGeneracion;
use App\Models\Workspace;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CentralGeneracionController extends Controller
{
    public function index(Request $request, Workspace $workspace): View
    {
        $this->ensureWorkspaceAccess($request, $workspace);

        $query = $workspace->centralesGeneracion()
            ->withCount(['ubicaciones', 'activos'])
            ->orderBy('nombre');

        if ($search = trim((string) $request->query('q', ''))) {
            $query->where(function ($inner) use ($search) {
                $inner->where('nombre', 'like', "%{$search}%")
                    ->orWhere('codigo', 'like', "%{$search}%")
                    ->orWhere('empresa_operadora', 'like', "%{$search}%");
            });
        }

        if ($tipo = $request->query('tipo_central')) {
            $query->where('tipo_central', $tipo);
        }

        $centrales = $query->paginate(15)->withQueryString();

        return view('centrales.index', [
            'workspace' => $workspace,
            'centrales' => $centrales,
            'tipoOptions' => $this->catalogItems('tipo_central_generacion'),
            'filters' => [
                'q' => $request->query('q'),
                'tipo_central' => $request->query('tipo_central'),
            ],
        ]);
    }

    public function create(Request $request, Workspace $workspace): View
    {
        $this->ensureWorkspaceAccess($request, $workspace);

        return view('centrales.create', [
            'workspace' => $workspace,
            'central' => new CentralGeneracion(['activo' => true, 'tipo_central' => 'HIDROELECTRICA']),
            'tipoOptions' => $this->catalogItems('tipo_central_generacion'),
        ]);
    }

    public function store(Request $request, Workspace $workspace): RedirectResponse
    {
        $this->ensureWorkspaceAccess($request, $workspace);

        $data = $this->validatedData($request, $workspace);
        $data['workspace_id'] = $workspace->id;
        $data['activo'] = $request->boolean('activo', true);

        $central = CentralGeneracion::create($data);

        return redirect()
            ->route('workspaces.centrales.show', [$workspace, $central])
            ->with('success', 'Central creada correctamente.');
    }

    public function show(Request $request, Workspace $workspace, CentralGeneracion $central): View
    {
        $this->ensureWorkspaceAccess($request, $workspace);
        $this->ensureBelongsToWorkspace($workspace, $central);

        $central->load([
            'ubicaciones' => fn ($query) => $query->orderBy('nombre'),
            'activos' => fn ($query) => $query->orderBy('nombre')->limit(25),
        ]);

        return view('centrales.show', compact('workspace', 'central'));
    }

    public function edit(Request $request, Workspace $workspace, CentralGeneracion $central): View
    {
        $this->ensureWorkspaceAccess($request, $workspace);
        $this->ensureBelongsToWorkspace($workspace, $central);

        return view('centrales.edit', [
            'workspace' => $workspace,
            'central' => $central,
            'tipoOptions' => $this->catalogItems('tipo_central_generacion'),
        ]);
    }

    public function update(Request $request, Workspace $workspace, CentralGeneracion $central): RedirectResponse
    {
        $this->ensureWorkspaceAccess($request, $workspace);
        $this->ensureBelongsToWorkspace($workspace, $central);

        $data = $this->validatedData($request, $workspace, $central);
        $data['activo'] = $request->boolean('activo', false);

        $central->update($data);

        return redirect()
            ->route('workspaces.centrales.show', [$workspace, $central])
            ->with('success', 'Central actualizada correctamente.');
    }

    public function destroy(Request $request, Workspace $workspace, CentralGeneracion $central): RedirectResponse
    {
        $this->ensureWorkspaceAccess($request, $workspace);
        $this->ensureBelongsToWorkspace($workspace, $central);

        $central->delete();

        return redirect()
            ->route('workspaces.centrales.index', $workspace)
            ->with('success', 'Central eliminada correctamente.');
    }

    protected function validatedData(Request $request, Workspace $workspace, ?CentralGeneracion $central = null): array
    {
        return $request->validate([
            'codigo' => [
                'required',
                'string',
                'max:50',
                Rule::unique('centrales_generacion', 'codigo')
                    ->where(fn ($query) => $query->where('workspace_id', $workspace->id))
                    ->ignore($central?->id),
            ],
            'nombre' => ['required', 'string', 'max:255'],
            'tipo_central' => ['required', 'string', 'max:30'],
            'capacidad_mw' => ['nullable', 'numeric', 'min:0'],
            'empresa_operadora' => ['nullable', 'string', 'max:255'],
            'ubicacion_referencia' => ['nullable', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string'],
            'activo' => ['nullable', 'boolean'],
        ]);
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
            (object) ['codigo' => 'HIDROELECTRICA', 'valor' => 'Hidroeléctrica'],
            (object) ['codigo' => 'TERMICA', 'valor' => 'Térmica'],
            (object) ['codigo' => 'SOLAR', 'valor' => 'Solar'],
            (object) ['codigo' => 'EOLICA', 'valor' => 'Eólica'],
            (object) ['codigo' => 'GEOTERMICA', 'valor' => 'Geotérmica'],
            (object) ['codigo' => 'BIOMASA', 'valor' => 'Biomasa'],
            (object) ['codigo' => 'SUBESTACION', 'valor' => 'Subestación'],
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

    protected function ensureBelongsToWorkspace(Workspace $workspace, CentralGeneracion $central): void
    {
        abort_unless($central->workspace_id === $workspace->id, 404);
    }
}
