<?php

namespace App\Http\Controllers;

use App\Models\Activo;
use App\Models\Catalogo;
use App\Models\Workspace;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ActivoController extends Controller
{
    public function index(Request $request, Workspace $workspace): View
    {
        $this->ensureWorkspaceAccess($request, $workspace);

        $query = $workspace->activos()
            ->with(['central', 'categoria', 'ubicacion', 'responsable'])
            ->orderBy('nombre');

        if ($search = trim((string) $request->query('q', ''))) {
            $query->where(function ($inner) use ($search) {
                $inner->where('nombre', 'like', "%{$search}%")
                    ->orWhere('codigo', 'like', "%{$search}%")
                    ->orWhere('tag', 'like', "%{$search}%")
                    ->orWhere('numero_serie', 'like', "%{$search}%");
            });
        }

        if ($estado = $request->query('estado')) {
            $query->where('estado_operativo', $estado);
        }

        if ($centralId = (int) $request->query('central_id', 0)) {
            $query->where('central_id', $centralId);
        }

        $activos = $query->paginate(15)->withQueryString();

        return view('activos.index', [
            'workspace' => $workspace,
            'activos' => $activos,
            'centrales' => $workspace->centralesGeneracion()->orderBy('nombre')->get(),
            'estadoOptions' => $this->catalogItems('estado_operativo_activo'),
            'filters' => [
                'q' => $request->query('q'),
                'estado' => $request->query('estado'),
                'central_id' => $request->query('central_id'),
            ],
        ]);
    }

    public function create(Request $request, Workspace $workspace): View
    {
        $this->ensureWorkspaceAccess($request, $workspace);

        return view('activos.create', [
            'workspace' => $workspace,
            'activo' => new Activo(['activo' => true, 'estado_operativo' => 'OPERATIVO', 'criticidad' => 'MEDIA']),
            ...$this->formOptions($workspace),
        ]);
    }

    public function store(Request $request, Workspace $workspace): RedirectResponse
    {
        $this->ensureWorkspaceAccess($request, $workspace);

        $data = $this->validatedData($request, $workspace);
        $data['workspace_id'] = $workspace->id;
        $data['activo'] = $request->boolean('activo', true);

        $activo = Activo::create($data);

        return redirect()
            ->route('workspaces.activos.show', [$workspace, $activo])
            ->with('success', 'Activo creado correctamente.');
    }

    public function show(Request $request, Workspace $workspace, Activo $activo): View
    {
        $this->ensureWorkspaceAccess($request, $workspace);
        $this->ensureActivoBelongsToWorkspace($workspace, $activo);

        $activo->load([
            'central',
            'categoria.parent',
            'ubicacion.parent',
            'responsable',
            'parent',
            'children',
            'contactos',
            'documentos.user',
            'eventos.user',
            'eventos.evidencias.user',
        ]);

        return view('activos.show', [
            'workspace' => $workspace,
            'activo' => $activo,
            'tipoEventoOptions' => $this->catalogItems('tipo_evento_activo'),
            'resultadoEventoOptions' => $this->catalogItems('resultado_evento_activo'),
        ]);
    }

    public function edit(Request $request, Workspace $workspace, Activo $activo): View
    {
        $this->ensureWorkspaceAccess($request, $workspace);
        $this->ensureActivoBelongsToWorkspace($workspace, $activo);

        return view('activos.edit', [
            'workspace' => $workspace,
            'activo' => $activo,
            ...$this->formOptions($workspace, $activo),
        ]);
    }

    public function update(Request $request, Workspace $workspace, Activo $activo): RedirectResponse
    {
        $this->ensureWorkspaceAccess($request, $workspace);
        $this->ensureActivoBelongsToWorkspace($workspace, $activo);

        $data = $this->validatedData($request, $workspace, $activo);
        $data['activo'] = $request->boolean('activo', false);

        $activo->update($data);

        return redirect()
            ->route('workspaces.activos.show', [$workspace, $activo])
            ->with('success', 'Activo actualizado correctamente.');
    }

    public function destroy(Request $request, Workspace $workspace, Activo $activo): RedirectResponse
    {
        $this->ensureWorkspaceAccess($request, $workspace);
        $this->ensureActivoBelongsToWorkspace($workspace, $activo);

        $activo->delete();

        return redirect()
            ->route('workspaces.activos.index', $workspace)
            ->with('success', 'Activo eliminado correctamente.');
    }

    protected function validatedData(Request $request, Workspace $workspace, ?Activo $activo = null): array
    {
        $responsableIds = $workspace->users()->pluck('users.id')->all();

        return $request->validate([
            'central_id' => [
                'required',
                'integer',
                Rule::exists('centrales_generacion', 'id')->where(
                    fn ($query) => $query->where('workspace_id', $workspace->id)
                ),
            ],
            'ubicacion_id' => [
                'nullable',
                'integer',
                Rule::exists('ubicaciones_activos', 'id')->where(
                    fn ($query) => $query->where('workspace_id', $workspace->id)
                ),
            ],
            'categoria_id' => [
                'required',
                'integer',
                Rule::exists('categorias_activos', 'id')->where(
                    fn ($query) => $query->where('workspace_id', $workspace->id)
                ),
            ],
            'parent_id' => [
                'nullable',
                'integer',
                Rule::exists('activos', 'id')->where(
                    fn ($query) => $query->where('workspace_id', $workspace->id)
                ),
            ],
            'responsable_user_id' => [
                'nullable',
                'integer',
                Rule::in($responsableIds),
            ],
            'codigo' => [
                'required',
                'string',
                'max:60',
                Rule::unique('activos', 'codigo')
                    ->where(fn ($query) => $query->where('workspace_id', $workspace->id))
                    ->ignore($activo?->id),
            ],
            'nombre' => ['required', 'string', 'max:255'],
            'tag' => [
                'nullable',
                'string',
                'max:80',
                Rule::unique('activos', 'tag')
                    ->where(fn ($query) => $query->where('workspace_id', $workspace->id))
                    ->ignore($activo?->id),
            ],
            'estado_operativo' => ['required', 'string', 'max:30'],
            'criticidad' => ['required', 'string', 'max:15'],
            'fabricante' => ['nullable', 'string', 'max:255'],
            'modelo' => ['nullable', 'string', 'max:255'],
            'numero_serie' => ['nullable', 'string', 'max:255'],
            'tipo_combustible' => ['nullable', 'string', 'max:30'],
            'proveedor_instalador' => ['nullable', 'string', 'max:255'],
            'fecha_fabricacion' => ['nullable', 'date'],
            'fecha_instalacion' => ['nullable', 'date'],
            'fecha_puesta_servicio' => ['nullable', 'date'],
            'potencia_nominal_kw' => ['nullable', 'numeric'],
            'voltaje_nominal_v' => ['nullable', 'numeric'],
            'corriente_nominal_a' => ['nullable', 'numeric'],
            'horas_operacion' => ['nullable', 'numeric', 'min:0'],
            'costo_adquisicion' => ['nullable', 'numeric', 'min:0'],
            'valor_libros' => ['nullable', 'numeric', 'min:0'],
            'descripcion' => ['nullable', 'string'],
            'activo' => ['nullable', 'boolean'],
        ]);
    }

    protected function formOptions(Workspace $workspace, ?Activo $activo = null): array
    {
        $responsables = $workspace->users()->orderBy('name')->get(['users.id', 'users.name']);

        return [
            'centrales' => $workspace->centralesGeneracion()->where('activo', true)->orderBy('nombre')->get(),
            'categorias' => $workspace->categoriasActivos()->where('activo', true)->orderBy('nombre')->get(),
            'ubicaciones' => $workspace->ubicacionesActivos()->where('activo', true)->orderBy('nombre')->get(),
            'activosPadre' => $workspace->activos()
                ->when($activo, fn ($query) => $query->whereKeyNot($activo->id))
                ->orderBy('nombre')
                ->get(),
            'responsables' => $responsables,
            'estadoOptions' => $this->catalogItems('estado_operativo_activo'),
            'criticidadOptions' => $this->catalogItems('criticidad_activo'),
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

        return match ($nombre) {
            'estado_operativo_activo' => collect([
                (object) ['codigo' => 'OPERATIVO', 'valor' => 'Operativo'],
                (object) ['codigo' => 'RESERVA', 'valor' => 'Reserva'],
                (object) ['codigo' => 'MANTENIMIENTO', 'valor' => 'Mantenimiento'],
                (object) ['codigo' => 'FALLA', 'valor' => 'Falla'],
                (object) ['codigo' => 'RETIRADO', 'valor' => 'Retirado'],
            ]),
            'criticidad_activo' => collect([
                (object) ['codigo' => 'BAJA', 'valor' => 'Baja'],
                (object) ['codigo' => 'MEDIA', 'valor' => 'Media'],
                (object) ['codigo' => 'ALTA', 'valor' => 'Alta'],
                (object) ['codigo' => 'CRITICA', 'valor' => 'Crítica'],
            ]),
            'resultado_evento_activo' => collect([
                (object) ['codigo' => 'OK', 'valor' => 'OK'],
                (object) ['codigo' => 'ALERTA', 'valor' => 'Alerta'],
                (object) ['codigo' => 'FALLA', 'valor' => 'Falla'],
                (object) ['codigo' => 'PENDIENTE', 'valor' => 'Pendiente'],
            ]),
            default => collect(),
        };
    }

    protected function ensureWorkspaceAccess(Request $request, Workspace $workspace): void
    {
        $allowed = $request->user()
            ->workspaces()
            ->where('workspaces.id', $workspace->id)
            ->exists();

        abort_unless($allowed, 403);
    }

    protected function ensureActivoBelongsToWorkspace(Workspace $workspace, Activo $activo): void
    {
        abort_unless($activo->workspace_id === $workspace->id, 404);
    }
}
