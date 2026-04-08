<?php
namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskEvidence;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TaskEvidenceController extends Controller
{
  public function index(Task $task)
  {
    $rows = TaskEvidence::where('task_id', $task->id)->get();

    return response()->json([
      'ok' => true,
      'items' => $rows->map(function($e){
        return [
          'item_id' => $e->nodo_item_id,
          'estado'  => $e->estado,
          'file'    => $e->path ? [
            'original_name' => $e->original_name,
            'size_bytes'    => $e->size_bytes,
            'created_at'    => optional($e->created_at)->toDateTimeString(),
            'download_url'  => \Storage::disk($e->disk)->url($e->path),
          ] : null
        ];
      })->values()
    ]);
  }

  public function store(Request $req, Task $task, $item)
  {
    $req->validate([
      'file' => ['required','file','max:20480'], // 20MB
    ]);

    // $item = nodo_item_id
    $file = $req->file('file');

    $path = $file->store("task_evidences/{$task->id}", 'public');

    $e = TaskEvidence::updateOrCreate(
      ['task_id' => $task->id, 'nodo_item_id' => (int)$item],
      [
        'estado'        => 'SUBIDO',
        'disk'          => 'public',
        'path'          => $path,
        'original_name' => $file->getClientOriginalName(),
        'size_bytes'    => $file->getSize(),
        'uploaded_by'   => auth()->id(),
      ]
    );
    $task->logActivity('evidence_uploaded', [
    'nodo_item_id'   => (int)$e->nodo_item_id,
    'evidence_id'    => (int)$e->id,
    'disk'           => (string)$e->disk,
    'path'           => (string)$e->path,
    'original_name'  => (string)$e->original_name,
    'size_bytes'     => (int)$e->size_bytes,
]);

    return response()->json([
      'ok' => true,
      'item' => [
        'item_id' => $e->nodo_item_id,
        'estado'  => $e->estado,
        'file'    => [
          'original_name' => $e->original_name,
          'size_bytes'    => $e->size_bytes,
          'created_at'    => optional($e->created_at)->toDateTimeString(),
          'download_url'  => \Storage::disk($e->disk)->url($e->path),
        ]
      ]
    ]);
  }
}