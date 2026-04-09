<?php

namespace App\Http\Controllers;

use App\Models\Activo;
use App\Models\ActivoDocumento;
use App\Models\Workspace;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ActivoDocumentoController extends Controller
{
    public function store(Request $request, Workspace $workspace, Activo $activo): RedirectResponse
    {
        $this->ensureAccess($request, $workspace, $activo);

        $data = $request->validate([
            'tipo_documento' => ['required', 'string', 'max:30', Rule::in(['IMAGEN', 'ESQUEMA', 'MAPA_VARIABLES', 'DATASHEET', 'INSTRUCCIONES', 'OTRO'])],
            'descripcion' => ['nullable', 'string'],
            'archivo' => ['required', 'file', 'max:15360'],
        ]);

        $file = $request->file('archivo');
        $safeName = Str::uuid()->toString() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs("activos/{$activo->id}/documentos", $safeName, 'public');

        ActivoDocumento::create([
            'activo_id' => $activo->id,
            'user_id' => $request->user()->id,
            'tipo_documento' => $data['tipo_documento'],
            'disk' => 'public',
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime' => $file->getClientMimeType(),
            'size_bytes' => $file->getSize(),
            'descripcion' => $data['descripcion'] ?? null,
        ]);

        return redirect()
            ->route('workspaces.activos.show', [$workspace, $activo])
            ->with('success', 'Documento cargado correctamente.');
    }

    public function download(Request $request, Workspace $workspace, Activo $activo, ActivoDocumento $documento)
    {
        $this->ensureAccess($request, $workspace, $activo, $documento);

        abort_unless(Storage::disk($documento->disk)->exists($documento->path), 404);

        return Storage::disk($documento->disk)->download($documento->path, $documento->original_name);
    }

    public function destroy(Request $request, Workspace $workspace, Activo $activo, ActivoDocumento $documento): RedirectResponse
    {
        $this->ensureAccess($request, $workspace, $activo, $documento);

        if (Storage::disk($documento->disk)->exists($documento->path)) {
            Storage::disk($documento->disk)->delete($documento->path);
        }

        $documento->delete();

        return redirect()
            ->route('workspaces.activos.show', [$workspace, $activo])
            ->with('success', 'Documento eliminado correctamente.');
    }

    protected function ensureAccess(Request $request, Workspace $workspace, Activo $activo, ?ActivoDocumento $documento = null): void
    {
        $allowed = $request->user()->workspaces()->where('workspaces.id', $workspace->id)->exists();
        abort_unless($allowed, 403);
        abort_unless($activo->workspace_id === $workspace->id, 404);
        if ($documento) {
            abort_unless($documento->activo_id === $activo->id, 404);
        }
    }
}
