<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\ItemExampleFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ItemExampleFileController extends Controller
{
    public function index(Item $item)
    {
        $files = ItemExampleFile::where('item_id', $item->id)
            ->orderByDesc('id')
            ->get()
            ->map(fn($f) => [
                'id' => $f->id,
                'original_name' => $f->original_name,
                'created_at' => optional($f->created_at)->format('Y-m-d H:i'),
                'download_url' => url("/process-builder/item-examples/{$f->id}/download"),
                'delete_url' => url("/process-builder/item-examples/{$f->id}"),
            ]);

        return response()->json(['files' => $files]);
    }

    public function store(Request $request, Item $item)
    {
        $request->validate([
            'examples' => 'required',
            'examples.*' => 'file|max:20480', // 20MB
        ]);

        foreach ($request->file('examples', []) as $file) {
            $name = uniqid() . '_' . $file->getClientOriginalName();

            // ✅ DISCO LOCAL (NO público)
            $path = $file->storeAs("item_examples/{$item->id}", $name, 'local');

            ItemExampleFile::create([
                'item_id' => $item->id,
                'user_id' => auth()->id(),
                'original_name' => $file->getClientOriginalName(),
                'path' => $path,
                'mime' => $file->getClientMimeType(),
                'size_bytes' => $file->getSize(),
            ]);
        }

        return response()->json(['ok' => true]);
    }

    public function download(ItemExampleFile $file)
    {
        // ✅ aquí más adelante puedes meter permisos por rol/proceso
        return Storage::disk('local')->download($file->path, $file->original_name);
    }

    public function destroy(ItemExampleFile $file)
    {
        Storage::disk('local')->delete($file->path);
        $file->delete();

        return response()->json(['ok' => true]);
    }
}