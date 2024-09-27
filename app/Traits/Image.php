<?php

namespace App\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Str;

trait Image
{
    public function uploadImage($image, string $path): string
    {
        if (!$image instanceof \Illuminate\Http\UploadedFile) {
            throw new \InvalidArgumentException('Invalid image file.');
        }

        // Generate a unique image name
        $imageName = uniqid() . '.' . $image->getClientOriginalExtension();
        $fullPath = $path . '/' . $imageName; // return xyz.png


        // Store the image in the specified path
        $image->storeAs($path, $imageName, 'public');
        $image->move(public_path($path), $imageName);

        $domain = env('APP_URL', 'http://localhost');
        // Create the standardized URL
        return $domain . '/' . $fullPath;
    }

    public function deleteImage(string $path): void
    {
        // Extract the relative path (e.g., 'assets/uploads/categories/Flame.jpg') from the URL
        $relativePath = parse_url($path, PHP_URL_PATH); // Returns '/assets/uploads/categories/Flame.jpg'

        // Remove the leading slash to match your storage structure
        $relativePath = ltrim($relativePath, '/');  // Now it's 'assets/uploads/categories/Flame.jpg'

        $storagePath = $relativePath;
        $publicPath = public_path($relativePath);

        if (Storage::disk('public')->exists($storagePath)) {
            Storage::disk('public')->delete($storagePath);
        }

        if (file_exists($publicPath)) {
            unlink($publicPath);
        }
    }

    public function updateImage(Request $request, string $inputName, string $path, ?string  $oldPath = null): string
    {
        if ($request->hasFile($inputName)) {
            if ($oldPath) {
                $this->deleteImage($oldPath);
            }

            return $this->uploadImage($request->file($inputName), $path);
        }

        throw new \RuntimeException('No image file provided for update.');
    }


    public function uploadMultiImage(Request $request, string $inputName, string $path): array
    {
        $imagePaths = [];

        if ($request->hasFile($inputName)) {
            $images = $request->file($inputName);

            foreach ($images as $image) {
                $imagePaths[] = $this->uploadImage($image, $path);
            }
        } else {
            throw new \RuntimeException('No image files provided for upload.');
        }

        return $imagePaths;
    }
}
