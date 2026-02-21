<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TaskFileController extends Controller
{
public function index(Task $task)
{
    $files = \App\Models\TaskFile::query()
        ->where('task_id', $task->id)
        ->with('user:id,name')
        ->orderByDesc('id')
        ->get()
        ->map(function ($f) use ($task) {
            return [
                'id'            => $f->id,
                'original_name' => $f->original_name,
                'mime'          => $f->mime,
                'size_bytes'    => (int) $f->size_bytes,
                'user_name'     => $f->user?->name,
                'download_url'  => route('tasks.files.download', [$task, $f]),
                'created_at'    => optional($f->created_at)->format('Y-m-d H:i'),
            ];
        });

    return response()->json([
        'ok'    => true,
        'task'  => ['id' => $task->id, 'title' => $task->title],
        'files' => $files,
    ]);
}


    public function store(Task $task, Request $request)
    {
        $request->validate([
            'files'   => ['required','array','min:1'],
            'files.*' => [
                'file',
                'max:10240', // 10MB por archivo (ajusta)
                'mimetypes:application/pdf,image/jpeg,image/png,image/webp,text/plain,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/zip'
            ],
        ], [
            'files.*.mimetypes' => 'Tipo de archivo no permitido.',
            'files.*.max'       => 'Archivo demasiado grande (máx 10MB).',
        ]);

        $saved = [];

        foreach ($request->file('files') as $file) {
            $original = $file->getClientOriginalName();
            $ext      = $file->getClientOriginalExtension();
            $mime     = $file->getClientMimeType();
            $size     = $file->getSize();

            $safeName = Str::uuid()->toString() . ($ext ? "." . $ext : "");
            $dir      = "tasks/{$task->id}";
            $path     = $file->storeAs($dir, $safeName, 'public');

            $row = TaskFile::create([
                'task_id'       => $task->id,
                'user_id'       => auth()->id(),
                'original_name' => $original,
                'path'          => $path,
                'mime'          => $mime,
                'size_bytes'    => $size,
            ]);

            $saved[] = [
                'id'            => $row->id,
                'original_name' => $row->original_name,
                'mime'          => $row->mime,
                'size_bytes'    => (int) $row->size_bytes,
                'user_name'     => auth()->user()?->name,
                'download_url'  => route('tasks.files.download', [$task, $row]),
                'created_at'    => optional($row->created_at)->format('Y-m-d H:i'),
            ];
        }

        return response()->json(['ok' => true, 'files' => $saved], 201);
    }

    public function download(Task $task, TaskFile $file)
    {
        // Seguridad: el file debe pertenecer a esa task
        if ((int)$file->task_id !== (int)$task->id) {
            abort(404);
        }

        if (!Storage::disk('public')->exists($file->path)) {
            abort(404, 'Archivo no encontrado en disco.');
        }

        return Storage::disk('public')->download($file->path, $file->original_name);
    }

    public function destroy(Task $task, TaskFile $file)
    {
        if ((int)$file->task_id !== (int)$task->id) {
            abort(404);
        }

        // (Opcional) solo permitir borrar al que subió o admin
        // if ($file->user_id !== auth()->id()) abort(403);

        if ($file->path && Storage::disk('public')->exists($file->path)) {
            Storage::disk('public')->delete($file->path);
        }

        $file->delete();

        return response()->json(['ok' => true]);
    }
}
