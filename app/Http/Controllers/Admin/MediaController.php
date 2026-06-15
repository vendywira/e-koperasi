<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaController extends Controller
{
    /**
     * List all uploaded media files.
     */
    public function index(Request $request): JsonResponse
    {
        $directory = $request->get('directory', 'cms');
        $disk = Storage::disk('public');

        $files = [];
        $directories = $disk->directories($directory);

        // Get files in this directory
        $filesInDir = $disk->files($directory);
        foreach ($filesInDir as $filePath) {
            $files[] = [
                'name' => basename($filePath),
                'path' => $filePath,
                'url' => Storage::disk('public')->url($filePath),
                'size' => $disk->size($filePath),
                'modified' => $disk->lastModified($filePath),
            ];
        }

        return response()->json([
            'files' => collect($files)->sortByDesc('modified')->values(),
            'directories' => $directories,
        ]);
    }

    /**
     * Upload a media file.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|max:5120|mimes:jpg,jpeg,png,gif,webp,svg,ico,pdf',
            'directory' => 'nullable|string|max:100',
        ]);

        $file = $request->file('file');
        $directory = $request->get('directory', 'cms');
        $filename = $file->getClientOriginalName();

        // Prevent overwriting — append timestamp if file exists
        $path = "{$directory}/{$filename}";
        if (Storage::disk('public')->exists($path)) {
            $name = pathinfo($filename, PATHINFO_FILENAME);
            $ext = $file->getClientOriginalExtension();
            $filename = "{$name}_" . time() . ".{$ext}";
            $path = "{$directory}/{$filename}";
        }

        $storedPath = $file->storeAs($directory, $filename, 'public');

        return response()->json([
            'success' => true,
            'file' => [
                'name' => $filename,
                'path' => $storedPath,
                'url' => Storage::disk('public')->url($storedPath),
                'size' => $file->getSize(),
            ],
        ]);
    }

    /**
     * Delete a media file.
     */
    public function destroy(Request $request): JsonResponse
    {
        $request->validate([
            'path' => 'required|string',
        ]);

        $path = $request->input('path');

        // Prevent path traversal — ensure path stays within the public disk
        $realPath = realpath(Storage::disk('public')->path($path));
        $diskRoot = realpath(Storage::disk('public')->path('.'));

        if (!$realPath || !$diskRoot || !str_starts_with($realPath, $diskRoot)) {
            return response()->json(['error' => 'Invalid path'], 403);
        }

        if (!Storage::disk('public')->exists($path)) {
            return response()->json(['error' => 'File not found'], 404);
        }

        Storage::disk('public')->delete($path);

        return response()->json([
            'success' => true,
            'message' => 'File deleted successfully.',
        ]);
    }
}
