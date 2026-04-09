<?php

namespace App\Http\Controllers;

use App\Models\Activo;
use App\Models\ActivoContacto;
use App\Models\Workspace;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ActivoContactoController extends Controller
{
    public function store(Request $request, Workspace $workspace, Activo $activo): RedirectResponse
    {
        $this->ensureAccess($request, $workspace, $activo);

        $data = $this->validatedData($request);
        $data['activo_id'] = $activo->id;
        $data['principal'] = $request->boolean('principal', false);

        if ($data['principal']) {
            $activo->contactos()->update(['principal' => false]);
        }

        ActivoContacto::create($data);

        return redirect()
            ->route('workspaces.activos.show', [$workspace, $activo])
            ->with('success', 'Contacto agregado correctamente.');
    }

    public function edit(Request $request, Workspace $workspace, Activo $activo, ActivoContacto $contacto): View
    {
        $this->ensureAccess($request, $workspace, $activo, $contacto);

        return view('activo_contactos.edit', compact('workspace', 'activo', 'contacto'));
    }

    public function update(Request $request, Workspace $workspace, Activo $activo, ActivoContacto $contacto): RedirectResponse
    {
        $this->ensureAccess($request, $workspace, $activo, $contacto);

        $data = $this->validatedData($request);
        $data['principal'] = $request->boolean('principal', false);

        if ($data['principal']) {
            $activo->contactos()->whereKeyNot($contacto->id)->update(['principal' => false]);
        }

        $contacto->update($data);

        return redirect()
            ->route('workspaces.activos.show', [$workspace, $activo])
            ->with('success', 'Contacto actualizado correctamente.');
    }

    public function destroy(Request $request, Workspace $workspace, Activo $activo, ActivoContacto $contacto): RedirectResponse
    {
        $this->ensureAccess($request, $workspace, $activo, $contacto);

        $contacto->delete();

        return redirect()
            ->route('workspaces.activos.show', [$workspace, $activo])
            ->with('success', 'Contacto eliminado correctamente.');
    }

    protected function validatedData(Request $request): array
    {
        return $request->validate([
            'tipo_contacto' => ['required', 'string', 'max:30', Rule::in(['GENERAL', 'PROVEEDOR', 'INSTALACION', 'MANTENIMIENTO', 'OPERACION', 'EMERGENCIA'])],
            'nombre' => ['required', 'string', 'max:255'],
            'cargo' => ['nullable', 'string', 'max:255'],
            'empresa' => ['nullable', 'string', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'notas' => ['nullable', 'string'],
            'principal' => ['nullable', 'boolean'],
        ]);
    }

    protected function ensureAccess(Request $request, Workspace $workspace, Activo $activo, ?ActivoContacto $contacto = null): void
    {
        $allowed = $request->user()->workspaces()->where('workspaces.id', $workspace->id)->exists();
        abort_unless($allowed, 403);
        abort_unless($activo->workspace_id === $workspace->id, 404);
        if ($contacto) {
            abort_unless($contacto->activo_id === $activo->id, 404);
        }
    }
}
