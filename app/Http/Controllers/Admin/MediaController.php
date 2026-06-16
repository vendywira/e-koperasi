<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;

class MediaController extends Controller
{
    /**
     * Display the dedicated media management page.
     */
    public function page()
    {
        return Inertia::render('Admin/Media/Index');
    }

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
            $webpUrl = null;
            $webpUrlSmall = null;
            $baseName = pathinfo(basename($filePath), PATHINFO_FILENAME);
            $webpDir = pathinfo($filePath, PATHINFO_DIRNAME);

            $webpName = $baseName . '.webp';
            $webpPath = ($webpDir === '.') ? $webpName : $webpDir . '/' . $webpName;
            if ($disk->exists($webpPath)) {
                $webpUrl = $disk->url($webpPath);
            }

            $webpSmallName = $baseName . '-sm.webp';
            $webpSmallPath = ($webpDir === '.') ? $webpSmallName : $webpDir . '/' . $webpSmallName;
            if ($disk->exists($webpSmallPath)) {
                $webpUrlSmall = $disk->url($webpSmallPath);
            }

            $files[] = [
                'name' => basename($filePath),
                'path' => $filePath,
                'url' => $disk->url($filePath),
                'webp_url' => $webpUrl,
                'webp_url_small' => $webpUrlSmall,
                'size' => $disk->size($filePath),
                'modified' => $disk->lastModified($filePath),
                'type' => $this->getFileType(basename($filePath)),
            ];
        }

        return response()->json([
            'files' => collect($files)->sortByDesc('modified')->values(),
            'directories' => $directories,
        ]);
    }

    /**
     * Upload a media file (image or video).
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|max:102400|mimes:jpg,jpeg,png,gif,webp,svg,ico,pdf,mp4,webm,ogg,mov,avi,wmv',
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

        // Generate WebP versions for convertible images
        $webpUrl = null;
        $webpUrlSmall = null;
        $extension = strtolower($file->getClientOriginalExtension());
        if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif'])) {
            // Full-size WebP
            $webpPath = $this->convertToWebP($storedPath, $filename);
            if ($webpPath) {
                $webpUrl = Storage::disk('public')->url($webpPath);
            }

            // Small WebP (resized to 320px wide)
            $webpSmallPath = $this->convertToWebP($storedPath, $filename, 320);
            if ($webpSmallPath) {
                $webpUrlSmall = Storage::disk('public')->url($webpSmallPath);
            }
        }

        return response()->json([
            'success' => true,
            'file' => [
                'name' => $filename,
                'path' => $storedPath,
                'url' => Storage::disk('public')->url($storedPath),
                'webp_url' => $webpUrl,
                'webp_url_small' => $webpUrlSmall,
                'size' => $file->getSize(),
                'type' => $this->getFileType($filename),
            ],
        ]);
    }

    /**
     * Rename a media file.
     */
    public function rename(Request $request): JsonResponse
    {
        $request->validate([
            'path' => 'required|string',
            'name' => 'required|string|max:255|regex:/^[\w\-. ()&,]+$/u',
        ]);

        $path = $request->input('path');
        $newName = trim($request->input('name'));

        // Prevent path traversal
        $disk = Storage::disk('public');
        $realPath = realpath($disk->path($path));
        $diskRoot = realpath($disk->path('.'));

        if (!$realPath || !$diskRoot || !str_starts_with($realPath, $diskRoot)) {
            return response()->json(['error' => 'Invalid path'], 403);
        }

        if (!$disk->exists($path)) {
            return response()->json(['error' => 'File not found'], 404);
        }

        $directory = dirname($path);
        $extension = pathinfo($path, PATHINFO_EXTENSION);

        // Ensure new name has the same extension
        $newExtension = pathinfo($newName, PATHINFO_EXTENSION);
        if (empty($newExtension)) {
            $newName .= '.' . $extension;
        } elseif (strtolower($newExtension) !== strtolower($extension)) {
            return response()->json(['error' => 'File extension cannot be changed'], 422);
        }

        // Clean the filename
        $newName = preg_replace('/[\x00-\x1f\x7f\x80-\x9f]/u', '', $newName);
        if (empty($newName)) {
            return response()->json(['error' => 'Invalid filename'], 422);
        }

        $newName = trim($newName);
        $newPath = ($directory === '.' ? '' : $directory . '/') . $newName;

        // Check if target already exists
        if ($disk->exists($newPath)) {
            return response()->json(['error' => 'A file with that name already exists'], 409);
        }

        if (!$disk->move($path, $newPath)) {
            return response()->json(['error' => 'Failed to rename file'], 500);
        }

        // Also rename WebP copies if they exist
        $oldBaseName = pathinfo(basename($path), PATHINFO_FILENAME);
        $newBaseName = pathinfo($newName, PATHINFO_FILENAME);
        $prefix = ($directory === '.' ? '' : $directory . '/');

        $webpUrl = null;
        $webpUrlSmall = null;

        // Full-size WebP
        $oldWebpPath = $prefix . $oldBaseName . '.webp';
        $newWebpPath = $prefix . $newBaseName . '.webp';
        if ($disk->exists($oldWebpPath)) {
            $disk->move($oldWebpPath, $newWebpPath);
            $webpUrl = $disk->url($newWebpPath);
        }

        // Small WebP
        $oldWebpSmallPath = $prefix . $oldBaseName . '-sm.webp';
        $newWebpSmallPath = $prefix . $newBaseName . '-sm.webp';
        if ($disk->exists($oldWebpSmallPath)) {
            $disk->move($oldWebpSmallPath, $newWebpSmallPath);
            $webpUrlSmall = $disk->url($newWebpSmallPath);
        }

        return response()->json([
            'success' => true,
            'file' => [
                'name' => $newName,
                'path' => $newPath,
                'url' => $disk->url($newPath),
                'webp_url' => $webpUrl,
                'webp_url_small' => $webpUrlSmall,
                'size' => $disk->size($newPath),
                'modified' => $disk->lastModified($newPath),
                'type' => $this->getFileType($newName),
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

        // Also delete WebP versions if they exist
        $baseName = pathinfo(basename($path), PATHINFO_FILENAME);
        $webpDir = pathinfo($path, PATHINFO_DIRNAME);

        $webpPath = ($webpDir === '.') ? $baseName . '.webp' : $webpDir . '/' . $baseName . '.webp';
        if (Storage::disk('public')->exists($webpPath)) {
            Storage::disk('public')->delete($webpPath);
        }

        $webpSmallPath = ($webpDir === '.') ? $baseName . '-sm.webp' : $webpDir . '/' . $baseName . '-sm.webp';
        if (Storage::disk('public')->exists($webpSmallPath)) {
            Storage::disk('public')->delete($webpSmallPath);
        }

        Storage::disk('public')->delete($path);

        return response()->json([
            'success' => true,
            'message' => 'File deleted successfully.',
        ]);
    }

    /**
     * Determine file type category from filename.
     */
    /**
     * Convert an uploaded image to WebP using GD.
     *
     * When $maxWidth is provided, the image is proportionally resized
     * and saved with a '-sm.webp' suffix (e.g. photo-sm.webp).
     */
    private function convertToWebP(string $path, string $filename, ?int $maxWidth = null): ?string
    {
        $disk = Storage::disk('public');
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if (!in_array($extension, ['jpg', 'jpeg', 'png', 'gif'])) {
            return null;
        }

        $fullPath = $disk->path($path);
        if (!file_exists($fullPath)) {
            return null;
        }

        // Create GD image from source
        $image = null;
        switch ($extension) {
            case 'jpg':
            case 'jpeg':
                $image = @imagecreatefromjpeg($fullPath);
                break;
            case 'png':
                $image = @imagecreatefrompng($fullPath);
                break;
            case 'gif':
                $image = @imagecreatefromgif($fullPath);
                break;
        }

        if (!$image) {
            return null;
        }

        // Preserve PNG transparency
        if ($extension === 'png') {
            imagealphablending($image, true);
            imagesavealpha($image, true);
        }

        // Resize proportionally if maxWidth is provided
        $workingImage = $image;
        if ($maxWidth !== null) {
            $origW = imagesx($image);
            $origH = imagesy($image);

            // Only resize if the image is wider than maxWidth
            if ($origW > $maxWidth) {
                $ratio = $maxWidth / $origW;
                $newW = (int) round($origW * $ratio);
                $newH = (int) round($origH * $ratio);

                $resized = @imagecreatetruecolor($newW, $newH);
                if ($resized) {
                    // Preserve transparency on the new canvas
                    if ($extension === 'png') {
                        imagealphablending($resized, true);
                        imagesavealpha($resized, true);
                    }
                    imagecopyresampled($resized, $image, 0, 0, 0, 0, $newW, $newH, $origW, $origH);
                    $workingImage = $resized;
                }
            }
        }

        // Determine output path
        $baseName = pathinfo($filename, PATHINFO_FILENAME);
        $suffix = $maxWidth !== null ? '-sm' : '';
        $webpFilename = $baseName . $suffix . '.webp';
        $directory = pathinfo($path, PATHINFO_DIRNAME);
        $webpPath = ($directory === '.') ? $webpFilename : $directory . '/' . $webpFilename;
        $webpFullPath = $disk->path($webpPath);

        // Convert to WebP at quality 80
        $quality = $maxWidth !== null ? 75 : 80;
        $success = @imagewebp($workingImage, $webpFullPath, $quality);

        // Clean up resized copy if different from original
        if ($workingImage !== $image) {
            imagedestroy($workingImage);
        }
        imagedestroy($image);

        if (!$success) {
            return null;
        }

        @chmod($webpFullPath, 0644);

        return $webpPath;
    }

    private function getFileType(string $filename): string
    {
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'ico'])) {
            return 'image';
        }

        if (in_array($ext, ['mp4', 'webm', 'ogg', 'mov', 'avi', 'wmv'])) {
            return 'video';
        }

        if (in_array($ext, ['pdf'])) {
            return 'document';
        }

        return 'other';
    }
}
