<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class ImageService
{
    protected $manager;

    public function __construct()
    {
        $this->manager = new ImageManager(new Driver());
    }

    /**
     * Upload and process an image file
     *
     * @param UploadedFile $file
     * @param string $directory
     * @param array $options
     * @return array
     */
    public function uploadAndProcess(UploadedFile $file, string $directory = 'images', array $options = []): array
    {
        $options = array_merge([
            'max_width' => 1200,
            'max_height' => 1200,
            'quality' => 85,
            'generate_webp' => true,
            'generate_thumbnails' => true,
            'thumbnail_sizes' => [
                'small' => [300, 300],
                'medium' => [600, 600],
                'large' => [900, 900]
            ]
        ], $options);

        // Generate unique filename
        $filename = $this->generateUniqueFilename($file);
        $extension = $file->getClientOriginalExtension();
        $originalPath = $directory . '/' . $filename . '.' . $extension;

        // Store original file
        $originalPath = $file->storeAs($directory, $filename . '.' . $extension, 'public');

        $result = [
            'original' => $originalPath,
            'webp' => null,
            'thumbnails' => []
        ];

        try {
            // Process the image
            $image = $this->manager->read(Storage::disk('public')->path($originalPath));

            // Resize if needed
            if ($image->width() > $options['max_width'] || $image->height() > $options['max_height']) {
                $image->scaleDown($options['max_width'], $options['max_height']);
            }

            // Save optimized original
            $image->save(Storage::disk('public')->path($originalPath), $options['quality']);

            // Generate WebP version
            if ($options['generate_webp']) {
                $webpPath = $directory . '/' . $filename . '.webp';
                $image->toWebp($options['quality'])->save(Storage::disk('public')->path($webpPath));
                $result['webp'] = $webpPath;
            }

            // Generate thumbnails
            if ($options['generate_thumbnails']) {
                foreach ($options['thumbnail_sizes'] as $sizeName => $dimensions) {
                    $thumbnailPath = $directory . '/thumbnails/' . $filename . '_' . $sizeName . '.webp';
                    
                    // Ensure thumbnails directory exists
                    Storage::disk('public')->makeDirectory($directory . '/thumbnails');
                    
                    $thumbnail = clone $image;
                    $thumbnail->cover($dimensions[0], $dimensions[1]);
                    $thumbnail->toWebp($options['quality'])->save(Storage::disk('public')->path($thumbnailPath));
                    
                    $result['thumbnails'][$sizeName] = $thumbnailPath;
                }
            }

        } catch (\Exception $e) {
            \Log::error('Image processing failed: ' . $e->getMessage());
            // Return original path if processing fails
        }

        return $result;
    }

    /**
     * Generate a unique filename
     *
     * @param UploadedFile $file
     * @return string
     */
    protected function generateUniqueFilename(UploadedFile $file): string
    {
        $extension = $file->getClientOriginalExtension();
        $name = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $name = \Str::slug($name);
        
        return $name . '_' . time() . '_' . \Str::random(8);
    }

    /**
     * Get the best image URL (WebP if available, otherwise original)
     *
     * @param string $originalPath
     * @param string $size
     * @return string
     */
    public function getImageUrl(string $originalPath, string $size = 'original'): string
    {
        if ($size === 'original') {
            // Try WebP first, fallback to original
            $webpPath = str_replace(['.jpg', '.jpeg', '.png'], '.webp', $originalPath);
            
            if (Storage::disk('public')->exists($webpPath)) {
                return Storage::disk('public')->url($webpPath);
            }
            
            return Storage::disk('public')->url($originalPath);
        }

        // For thumbnails, always use WebP
        $thumbnailPath = str_replace(['.jpg', '.jpeg', '.png'], '_' . $size . '.webp', $originalPath);
        $thumbnailPath = str_replace(basename($thumbnailPath), 'thumbnails/' . basename($thumbnailPath), $thumbnailPath);
        
        if (Storage::disk('public')->exists($thumbnailPath)) {
            return Storage::disk('public')->url($thumbnailPath);
        }

        // Fallback to original
        return Storage::disk('public')->url($originalPath);
    }

    /**
     * Delete image and all its variants
     *
     * @param string $originalPath
     * @return bool
     */
    public function deleteImage(string $originalPath): bool
    {
        $deleted = true;

        // Delete original
        if (Storage::disk('public')->exists($originalPath)) {
            $deleted = Storage::disk('public')->delete($originalPath) && $deleted;
        }

        // Delete WebP version
        $webpPath = str_replace(['.jpg', '.jpeg', '.png'], '.webp', $originalPath);
        if (Storage::disk('public')->exists($webpPath)) {
            $deleted = Storage::disk('public')->delete($webpPath) && $deleted;
        }

        // Delete thumbnails
        $thumbnailSizes = ['small', 'medium', 'large'];
        foreach ($thumbnailSizes as $size) {
            $thumbnailPath = str_replace(['.jpg', '.jpeg', '.png'], '_' . $size . '.webp', $originalPath);
            $thumbnailPath = str_replace(basename($thumbnailPath), 'thumbnails/' . basename($thumbnailPath), $thumbnailPath);
            
            if (Storage::disk('public')->exists($thumbnailPath)) {
                $deleted = Storage::disk('public')->delete($thumbnailPath) && $deleted;
            }
        }

        return $deleted;
    }

    /**
     * Get image dimensions
     *
     * @param string $path
     * @return array|null
     */
    public function getImageDimensions(string $path): ?array
    {
        try {
            $image = $this->manager->read(Storage::disk('public')->path($path));
            return [
                'width' => $image->width(),
                'height' => $image->height()
            ];
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Validate image file
     *
     * @param UploadedFile $file
     * @param array $rules
     * @return array
     */
    public function validateImage(UploadedFile $file, array $rules = []): array
    {
        $defaultRules = [
            'max_size' => 5 * 1024 * 1024, // 5MB
            'allowed_types' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
            'min_dimensions' => [100, 100],
            'max_dimensions' => [4000, 4000]
        ];

        $rules = array_merge($defaultRules, $rules);
        $errors = [];

        // Check file size
        if ($file->getSize() > $rules['max_size']) {
            $errors[] = 'File size must be less than ' . ($rules['max_size'] / 1024 / 1024) . 'MB';
        }

        // Check file type
        $extension = strtolower($file->getClientOriginalExtension());
        if (!in_array($extension, $rules['allowed_types'])) {
            $errors[] = 'File type not allowed. Allowed types: ' . implode(', ', $rules['allowed_types']);
        }

        // Check dimensions
        try {
            $image = $this->manager->read($file->getPathname());
            $width = $image->width();
            $height = $image->height();

            if ($width < $rules['min_dimensions'][0] || $height < $rules['min_dimensions'][1]) {
                $errors[] = 'Image dimensions must be at least ' . $rules['min_dimensions'][0] . 'x' . $rules['min_dimensions'][1];
            }

            if ($width > $rules['max_dimensions'][0] || $height > $rules['max_dimensions'][1]) {
                $errors[] = 'Image dimensions must be less than ' . $rules['max_dimensions'][0] . 'x' . $rules['max_dimensions'][1];
            }
        } catch (\Exception $e) {
            $errors[] = 'Invalid image file';
        }

        return $errors;
    }
}
