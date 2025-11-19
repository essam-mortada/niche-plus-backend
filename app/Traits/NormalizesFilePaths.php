<?php

namespace App\Traits;

trait NormalizesFilePaths
{
    /**
     * Normalize file path by removing domain and storage prefix
     * Converts: http://domain.com/storage/uploads/file.jpg
     * To: uploads/file.jpg
     */
    protected function normalizePath($path)
    {
        if (!$path || empty($path)) {
            return $path;
        }

        // Remove domain if present (http://... or https://...)
        $path = preg_replace('#^https?://[^/]+/#', '', $path);
        
        // Remove 'storage/' prefix if present
        $path = preg_replace('#^storage/#', '', $path);
        
        return $path;
    }

    /**
     * Normalize multiple file paths in an array
     */
    protected function normalizeFilePaths(array $data, array $fields)
    {
        foreach ($fields as $field) {
            if (isset($data[$field]) && $data[$field]) {
                $data[$field] = $this->normalizePath($data[$field]);
            }
        }
        
        return $data;
    }
}
