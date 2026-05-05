<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;

class StorageSetupController extends Controller
{
    /**
     * Show storage setup page
     */
    public function index()
    {
        $storagePath = storage_path('app/public');
        $publicStorage = public_path('storage');
        
        $status = [
            'storage_exists' => File::exists($storagePath),
            'storage_writable' => File::isWritable($storagePath),
            'public_writable' => File::isWritable(public_path()),
            'symlink_exists' => File::exists($publicStorage),
            'is_symlink' => is_link($publicStorage),
            'symlink_target' => is_link($publicStorage) ? readlink($publicStorage) : null,
        ];
        
        return view('admin.storage-setup', compact('status'));
    }
    
    /**
     * Create storage symlink
     */
    public function createSymlink(Request $request)
    {
        $storagePath = storage_path('app/public');
        $publicStorage = public_path('storage');
        
        try {
            // Ensure storage directory exists
            if (!File::exists($storagePath)) {
                File::makeDirectory($storagePath, 0755, true);
            }
            
            // Remove existing file/directory if it exists
            if (File::exists($publicStorage)) {
                if (is_dir($publicStorage)) {
                    File::deleteDirectory($publicStorage);
                } else {
                    File::delete($publicStorage);
                }
            }
            
            // Create symlink
            if (symlink($storagePath, $publicStorage)) {
                return response()->json([
                    'success' => true,
                    'message' => 'Storage symlink created successfully!',
                    'symlink' => $publicStorage,
                    'target' => $storagePath
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create symlink. Check server permissions.',
                    'suggestion' => 'Try using cPanel File Manager or contact your hosting provider.'
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Test storage access
     */
    public function testStorage(Request $request)
    {
        $publicStorage = public_path('storage');
        $testFile = $publicStorage . '/test-' . time() . '.txt';
        $testContent = 'Storage test file created at ' . now();
        
        try {
            if (file_put_contents($testFile, $testContent)) {
                $testUrl = url('storage/test-' . basename($testFile));
                
                // Clean up test file
                unlink($testFile);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Storage test successful!',
                    'test_url' => $testUrl
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to write test file. Check permissions.'
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Run artisan storage:link command
     */
    public function runArtisanCommand(Request $request)
    {
        try {
            $exitCode = Artisan::call('storage:link');
            
            if ($exitCode === 0) {
                return response()->json([
                    'success' => true,
                    'message' => 'Artisan storage:link command executed successfully!',
                    'output' => Artisan::output()
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Artisan command failed with exit code: ' . $exitCode,
                    'output' => Artisan::output()
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error running artisan command: ' . $e->getMessage()
            ], 500);
        }
    }
}






