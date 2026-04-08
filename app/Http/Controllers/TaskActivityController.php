<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;

class TaskActivityController extends Controller
{
    public function index(Task $task, Request $request)
    {
        if (!$request->expectsJson() && !$request->ajax()) {
            return response()->json(['ok' => false, 'message' => 'Bad request'], 400);
        }

        $activities = $task->activities()
            ->with(['user:id,name'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($a) {
                return [
                    'id'         => (int) $a->id,
                    'event'      => (string) $a->event,
                    'meta'       => $a->meta ?? [],
                    'user_name'  => $a->user?->name ?? 'Sistema',
                    'created_at' => optional($a->created_at)->format('Y-m-d H:i:s'),
                ];
            })
            ->values();

        return response()->json([
            'ok'   => true,
            'task' => [
                'id'    => (int) $task->id,
                'title' => (string) $task->title,
            ],
            'activities' => $activities,
        ]);
    }
}