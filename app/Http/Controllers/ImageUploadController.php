<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageUploadController extends Controller
{
    public function upload(Request $request)
    {
        try {
            \Log::info('Image upload request received', [
                'has_file' => $request->hasFile('image'),
                'type' => $request->input('type'),
                'all_files' => $request->allFiles()
            ]);

            $request->validate([
                'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120', // 5MB max
                'type' => 'required|in:post,offer,issue,award'
            ]);

            $image = $request->file('image');
            $type = $request->input('type');
            
            if (!$image) {
                return response()->json([
                    'success' => false,
                    'message' => 'No image file received'
                ], 400);
            }
            
            // Generate unique filename
            $filename = Str::random(40) . '.' . $image->getClientOriginalExtension();
            
            // Ensure directory exists
            $directory = "uploads/{$type}s";
            if (!Storage::disk('public')->exists($directory)) {
                Storage::disk('public')->makeDirectory($directory);
            }
            
            // Store in public/uploads/{type}s
            $path = $image->storeAs($directory, $filename, 'public');
            
            // Return full URL
            $url = url('storage/' . $path);
            
            \Log::info('Image uploaded successfully', [
                'path' => $path,
                'url' => $url
            ]);
            
            return response()->json([
                'success' => true,
                'url' => $url,
                'path' => $path
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Image upload validation error: ' . json_encode($e->errors()));
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Image upload error: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload image: ' . $e->getMessage()
            ], 500);
        }
    }

    public function uploadPDF(Request $request)
    {
        try {
            \Log::info('PDF upload request received', [
                'has_file' => $request->hasFile('pdf'),
                'all_files' => $request->allFiles()
            ]);

            $request->validate([
                'pdf' => 'required|mimes:pdf|max:20480', // 20MB max for PDFs
            ]);

            $pdf = $request->file('pdf');
            
            if (!$pdf) {
                return response()->json([
                    'success' => false,
                    'message' => 'No PDF file received'
                ], 400);
            }
            
            // Generate unique filename
            $filename = Str::random(40) . '.pdf';
            
            // Ensure directory exists
            $directory = "uploads/pdfs";
            if (!Storage::disk('public')->exists($directory)) {
                Storage::disk('public')->makeDirectory($directory);
            }
            
            // Store in public/uploads/pdfs
            $path = $pdf->storeAs($directory, $filename, 'public');
            
            // Return full URL
            $url = url('storage/' . $path);
            
            \Log::info('PDF uploaded successfully', [
                'path' => $path,
                'url' => $url
            ]);
            
            return response()->json([
                'success' => true,
                'url' => $url,
                'path' => $path
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('PDF upload validation error: ' . json_encode($e->errors()));
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('PDF upload error: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload PDF: ' . $e->getMessage()
            ], 500);
        }
    }
}
