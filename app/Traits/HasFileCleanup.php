<?php

namespace App\Traits;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

trait HasFileCleanup
{
    /**
     * Boot the trait
     */
    protected static function bootHasFileCleanup()
    {
        // When updating, delete old files if they changed
        static::updating(function ($model) {
            if (isset($model->fileFields)) {
                foreach ($model->fileFields as $field) {
                    if ($model->isDirty($field) && $model->getOriginal($field)) {
                        static::deleteFile($model->getOriginal($field));
                    }
                }
            }
        });

        // When deleting, delete all associated files
        static::deleting(function ($model) {
            if (isset($model->fileFields)) {
                foreach ($model->fileFields as $field) {
                    if ($model->$field) {
                        static::deleteFile($model->$field);
                    }
                }
            }
        });
    }

    /**
     * Delete a file from storage
     */
    protected static function deleteFile($path)
    {
        if (!$path) {
            return;
        }

        // Skip if it's a full URL (external file)
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return;
        }

        // Remove 'storage/' prefix if present
        $path = str_replace('storage/', '', $path);

        try {
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
                Log::info("🗑️ Deleted file: {$path}");
            }
        } catch (\Exception $e) {
            Log::error("❌ Failed to delete file {$path}: " . $e->getMessage());
        }
    }
}
