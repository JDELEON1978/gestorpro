<?php

namespace App\Http\Controllers;

use App\Models\CategoriaActivo;
use App\Models\Workspace;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CategoriaActivoController extends Controller
{
    public function index(Request $request, Workspace $workspace): View
    {
        $this->ensureWorkspaceAccess($request, $workspace);

        $query = $workspace->categoriasActivos()
            ->with(['parent'])
            ->withCount(['children', 'activos'])
            ->orderBy('nombre');

        if ($search = trim((string) $request->query('q', ''))) {
            $query->where(function ($inner) use ($search) {
                $inner->where('nombre', 'like', "%{$search}%")
                    ->orWhere('codigo', 'like', "%{$search}%");
            });
        }

        if ($clase = $request->query('clase_activo')) {
            $query->where('clase_activo', $clase);
        }

        $categorias = $query->paginate(15)->withQueryString();

        return view('categorias.index', [
            'workspace' => $workspace,
            'categorias' => $categorias,
            'claseOptions' => $this->classOptions(),
            'filters' => [
                'q' => $request->query('q'),
                'clase_activo' => $request->query('clase_activo'),
            ],
        ]);
    }

    public function create(Request $request, Workspace $workspace): View
    {
        $this->ensureWorkspaceAccess($request, $workspace);

        return view('categorias.create', [
            'workspace' => $workspace,
            'categoria' => new CategoriaActivo(['activo' => true, 'clase_activo' => 'EQUIPO', 'requiere_serie' => false]),
            ...$this->formOptions($workspace),
        ]);
    }

    public function store(Request $request, Workspace $workspace): RedirectResponse
    {
        $this->ensureWorkspaceAccess($request, $workspace);

        $data = $this->validatedData($request, $workspace);
        $data['workspace_id'] = $workspace->id;
        $data['activo'] = $request->boolean('activo', true);
        $data['requiere_serie'] = $request->boolean('requiere_serie', false);

        $categoria = CategoriaActivo::create($data);

        return redirect()
            ->route('workspaces.categorias.show', [$workspace, $categoria])
            ->with('success', 'Categoría creada correctamente.');
    }

    public function show(Request $request, Workspace $workspace, CategoriaActivo $categoria): View
    {
        $this->ensureWorkspaceAccess($request, $workspace);
        $this->ensureBelongsToWorkspace($workspace, $categoria);

        $categoria->load([
            'parent',
            'children' => fn ($query) => $query->orderBy('nombre'),
            'activos' => fn ($query) => $query->orderBy('nombre')->limit(25),
        ]);

        return view('categorias.show', [
            'workspace' => $workspace,
            'categoria' => $categoria,
            'claseOptions' => $this->classOptions(),
        ]);
    }

    public function edit(Request $request, Workspace $workspace, CategoriaActivo $categoria): View
    {
        $this->ensureWorkspaceAccess($request, $workspace);
        $this->ensureBelongsToWorkspace($workspace, $categoria);

        return view('categorias.edit', [
            'workspace' => $workspace,
            'categoria' => $categoria,
            ...$this->formOptions($workspace, $categoria),
        ]);
    }

    public function update(Request $request, Workspace $workspace, CategoriaActivo $categoria): RedirectResponse
    {
        $this->ensureWorkspaceAccess($request, $workspace);
        $this->ensureBelongsToWorkspace($workspace, $categoria);

        $data = $this->validatedData($request, $workspace, $categoria);
        $data['activo'] = $request->boolean('activo', false);
        $data['requiere_serie'] = $request->boolean('requiere_serie', false);

        $categoria->update($data);

        return redirect()
            ->route('workspaces.categorias.show', [$workspace, $categoria])
            ->with('success', 'Categoría actualizada correctamente.');
    }

    public function destroy(Request $request, Workspace $workspace, CategoriaActivo $categoria): RedirectResponse
    {
        $this->ensureWorkspaceAccess($request, $workspace);
        $this->ensureBelongsToWorkspace($workspace, $categoria);

        $categoria->delete();

        return redirect()
            ->route('workspaces.categorias.index', $workspace)
            ->with('success', 'Categoría eliminada correctamente.');
    }

    protected function validatedData(Request $request, Workspace $workspace, ?CategoriaActivo $categoria = null): array
    {
        return $request->validate([
            'parent_id' => [
                'nullable',
                'integer',
                Rule::exists('categorias_activos', 'id')->where(
                    fn ($query) => $query->where('workspace_id', $workspace->id)
                ),
            ],
            'codigo' => [
                'required',
                'string',
                'max:50',
                Rule::unique('categorias_activos', 'codigo')
                    ->where(fn ($query) => $query->where('workspace_id', $workspace->id))
                    ->ignore($categoria?->id),
            ],
            'nombre' => ['required', 'string', 'max:255'],
            'clase_activo' => ['required', 'string', 'max:30'],
            'requiere_serie' => ['nullable', 'boolean'],
            'vida_util_anios' => ['nullable', 'integer', 'min:1', 'max:100'],
            'descripcion' => ['nullable', 'string'],
            'activo' => ['nullable', 'boolean'],
        ]);
    }

    protected function formOptions(Workspace $workspace, ?CategoriaActivo $categoria = null): array
    {
        return [
            'categoriasPadre' => $workspace->categoriasActivos()
                ->when($categoria, fn ($query) => $query->whereKeyNot($categoria->id))
                ->orderBy('nombre')
                ->get(),
            'claseOptions' => $this->classOptions(),
        ];
    }

    protected function classOptions(): array
    {
        return [
            'EQUIPO' => 'Equipo',
            'COMPONENTE' => 'Componente',
            'SISTEMA' => 'Sistema',
            'HERRAMIENTA' => 'Herramienta',
            'INFRAESTRUCTURA' => 'Infraestructura',
        ];
    }

    protected function ensureWorkspaceAccess(Request $request, Workspace $workspace): void
    {
        $allowed = $request->user()
            ->workspaces()
            ->where('workspaces.id', $workspace->id)
            ->exists();

        abort_unless($allowed, 403);
    }

    protected function ensureBelongsToWorkspace(Workspace $workspace, CategoriaActivo $categoria): void
    {
        abort_unless($categoria->workspace_id === $workspace->id, 404);
    }
}
