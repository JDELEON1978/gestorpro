<?php

namespace App\Http\Controllers;

use App\Models\Activo;
use App\Models\ActivoEvento;
use App\Models\ActivoEventoEvidencia;
use App\Models\Workspace;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ActivoEventoEvidenciaController extends Controller
{
    public function store(Request $request, Workspace $workspace, Activo $activo, ActivoEvento $evento): RedirectResponse
    {
        $this->ensureAccess($request, $workspace, $activo, $evento);

        $data = $request->validate([
            'archivo' => ['required', 'file', 'max:15360'],
            'descripcion' => ['nullable', 'string'],
        ]);

        $file = $request->file('archivo');
        $safeName = Str::uuid()->toString() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs("activos/{$activo->id}/eventos/{$evento->id}", $safeName, 'public');

        ActivoEventoEvidencia::create([
            'activo_evento_id' => $evento->id,
            'user_id' => $request->user()->id,
            'disk' => 'public',
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime' => $file->getClientMimeType(),
            'size_bytes' => $file->getSize(),
            'descripcion' => $data['descripcion'] ?? null,
        ]);

        return redirect()
            ->route('workspaces.activos.eventos.edit', [$workspace, $activo, $evento])
            ->with('success', 'Evidencia cargada correctamente.');
    }

    public function download(Request $request, Workspace $workspace, Activo $activo, ActivoEvento $evento, ActivoEventoEvidencia $evidencia)
    {
        $this->ensureAccess($request, $workspace, $activo, $evento, $evidencia);

        abort_unless(Storage::disk($evidencia->disk)->exists($evidencia->path), 404);

        return Storage::disk($evidencia->disk)->download($evidencia->path, $evidencia->original_name);
    }

    public function destroy(Request $request, Workspace $workspace, Activo $activo, ActivoEvento $evento, ActivoEventoEvidencia $evidencia): RedirectResponse
    {
        $this->ensureAccess($request, $workspace, $activo, $evento, $evidencia);

        if (Storage::disk($evidencia->disk)->exists($evidencia->path)) {
            Storage::disk($evidencia->disk)->delete($evidencia->path);
        }

        $evidencia->delete();

        return redirect()
            ->route('workspaces.activos.eventos.edit', [$workspace, $activo, $evento])
            ->with('success', 'Evidencia eliminada correctamente.');
    }

    protected function ensureAccess(Request $request, Workspace $workspace, Activo $activo, ActivoEvento $evento, ?ActivoEventoEvidencia $evidencia = null): void
    {
        $allowed = $request->user()->workspaces()->where('workspaces.id', $workspace->id)->exists();
        abort_unless($allowed, 403);
        abort_unless($activo->workspace_id === $workspace->id, 404);
        abort_unless($evento->activo_id === $activo->id, 404);
        if ($evidencia) {
            abort_unless($evidencia->activo_evento_id === $evento->id, 404);
        }
    }
}
