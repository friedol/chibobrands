<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Services\SmsApiService;

class SettingsController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        // Middleware is handled via route middleware
    }
    
    /**
     * Authorize access - only super_admin
     */
    private function checkSuperAdmin()
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'super_admin') {
            abort(403, 'Only super administrators can access settings.');
        }
    }

    /**
     * Display SMS settings page
     */
    public function smsSettings()
    {
        $this->checkSuperAdmin();
        $currentSettings = [
            'api_key' => config('services.sms.api_key'),
            'api_secret' => config('services.sms.api_secret'),
            'api_url' => config('services.sms.api_url'),
            'sender_id' => config('services.sms.sender_id'),
        ];

        return view('admin.settings.sms', [
            'settings' => $currentSettings,
        ]);
    }

    /**
     * Update SMS settings
     */
    public function updateSmsSettings(Request $request)
    {
        $this->checkSuperAdmin();
        
        // Handle config cache clearing request
        if ($request->has('clear_cache') && $request->boolean('clear_cache')) {
            try {
                Artisan::call('config:clear');
                return response()->json([
                    'success' => true,
                    'message' => 'Configuration cache cleared successfully.',
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to clear config cache', [
                    'error' => $e->getMessage(),
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to clear cache: ' . $e->getMessage(),
                ], 500);
            }
        }
        
        $validated = $request->validate([
            'api_key' => 'required|string|max:255',
            'api_secret' => 'nullable|string|max:500',
            'api_url' => 'required|url|max:255',
            'sender_id' => 'required|string|max:50',
        ]);

        // Update .env file
        $envFile = base_path('.env');
        
        if (!file_exists($envFile)) {
            return redirect()->back()
                ->withErrors(['error' => '.env file not found. Please contact system administrator.']);
        }
        
        try {
            $envContent = file_get_contents($envFile);
            
            // Update or add SMS settings (trim values to remove whitespace)
            $settings = [
                'SMS_API_KEY' => trim($validated['api_key']),
                'SMS_API_SECRET' => trim($validated['api_secret'] ?? ''),
                'SMS_API_URL' => trim($validated['api_url']),
                'SMS_SENDER_ID' => trim($validated['sender_id']),
            ];

            foreach ($settings as $key => $value) {
                // Escape special characters in value
                $escapedValue = preg_quote($value, '/');
                
                // Pattern to match existing key (handles both quoted and unquoted values)
                $pattern = "/^{$key}=[\"']?.*?[\"']?\s*$/m";
                
                if (preg_match($pattern, $envContent)) {
                    // Replace existing value
                    $envContent = preg_replace($pattern, "{$key}=\"{$value}\"", $envContent);
                } else {
                    // Add new key at the end (before empty lines at the end)
                    if (!preg_match("/^{$key}=/m", $envContent)) {
                        $envContent = rtrim($envContent) . "\n{$key}=\"{$value}\"\n";
                    }
                }
            }

            // Write back to file
            if (file_put_contents($envFile, $envContent) === false) {
                throw new \Exception('Failed to write to .env file');
            }
            
            // Clear config cache
            Artisan::call('config:clear');
        } catch (\Exception $e) {
            Log::error('Failed to update SMS settings in .env', [
                'error' => $e->getMessage(),
            ]);
            
            return redirect()->back()
                ->withErrors(['error' => 'Failed to update settings: ' . $e->getMessage()]);
        }

        // Log the settings update
        try {
            \App\Services\AuditLogService::log(
                'updated',
                'SMS configuration updated',
                Auth::user(),
                null,
                ['api_key' => substr($validated['api_key'], 0, 8) . '...', 'sender_id' => $validated['sender_id']]
            );
        } catch (\Exception $e) {
            Log::warning('Failed to log SMS settings update: ' . $e->getMessage());
        }

        return redirect()->route('admin.settings.sms')
            ->with('success', 'SMS settings updated successfully!');
    }

    /**
     * Test SMS configuration
     */
    public function diagnoseSms()
    {
        $this->checkSuperAdmin();
        
        // Read directly from .env file for accurate diagnosis
        $envFile = base_path('.env');
        $envDirect = [];
        if (file_exists($envFile)) {
            $envContent = file_get_contents($envFile);
            foreach (['SMS_API_KEY', 'SMS_API_SECRET', 'SMS_SENDER_ID', 'SMS_API_URL'] as $key) {
                if (preg_match("/^{$key}=[\"']?([^\"'\n\r]*)[\"']?/m", $envContent, $matches)) {
                    $val = trim($matches[1] ?? '');
                    $envDirect[$key] = [
                        'value' => substr($val, 0, 8) . '...',
                        'length' => strlen($val),
                        'has_quotes_in_file' => preg_match("/^{$key}=[\"']/m", $envContent),
                        'is_base64_encoded' => base64_decode($val, true) !== false && base64_encode(base64_decode($val, true)) === $val,
                    ];
                }
            }
        }
        
        $diagnostics = [
            'environment' => [
                'app_env' => config('app.env'),
                'config_cached' => app()->configurationIsCached(),
                'config_path' => config_path('services.php'),
                'env_file_exists' => file_exists(base_path('.env')),
                'env_file_readable' => file_exists(base_path('.env')) ? is_readable(base_path('.env')) : false,
            ],
            'env_direct_read' => $envDirect,
            'credentials' => [
                'sms_api_key_set' => !empty(env('SMS_API_KEY')),
                'sms_api_key_length' => strlen(env('SMS_API_KEY') ?? ''),
                'sms_api_key_preview' => substr(env('SMS_API_KEY') ?? '', 0, 8) . '...',
                'sms_api_secret_set' => !empty(env('SMS_API_SECRET')),
                'sms_api_secret_length' => strlen(env('SMS_API_SECRET') ?? ''),
                'sms_api_secret_is_base64' => !empty(env('SMS_API_SECRET')) ? (base64_decode(env('SMS_API_SECRET'), true) !== false) : false,
                'sms_sender_id' => env('SMS_SENDER_ID'),
                'sms_api_url' => env('SMS_API_URL'),
            ],
            'config_values' => [
                'config_api_key_set' => !empty(config('services.sms.api_key')),
                'config_api_key_length' => strlen(config('services.sms.api_key') ?? ''),
                'config_api_key_preview' => substr(config('services.sms.api_key') ?? '', 0, 8) . '...',
                'config_api_secret_set' => !empty(config('services.sms.api_secret')),
                'config_api_secret_length' => strlen(config('services.sms.api_secret') ?? ''),
                'config_sender_id' => config('services.sms.sender_id'),
                'config_api_url' => config('services.sms.api_url'),
            ],
            'recommendations' => [],
        ];
        
        // Add recommendations
        if ($diagnostics['environment']['config_cached']) {
            $diagnostics['recommendations'][] = 'Config is cached. Run: php artisan config:clear';
        }
        
        if (!$diagnostics['environment']['env_file_exists']) {
            $diagnostics['recommendations'][] = 'WARNING: .env file not found in ' . base_path();
        }
        
        if (!$diagnostics['credentials']['sms_api_key_set']) {
            $diagnostics['recommendations'][] = 'SMS_API_KEY not set in .env file';
        }
        
        if (!$diagnostics['credentials']['sms_api_secret_set']) {
            $diagnostics['recommendations'][] = 'SMS_API_SECRET not set in .env file';
        }
        
        if ($diagnostics['credentials']['sms_api_key_set'] && !$diagnostics['config_values']['config_api_key_set']) {
            $diagnostics['recommendations'][] = 'Env variable set but config not reading it. Clear config cache.';
        }
        
        // Add additional recommendations based on secret key format
        if (isset($diagnostics['env_direct_read']['SMS_API_SECRET'])) {
            $secretLength = $diagnostics['env_direct_read']['SMS_API_SECRET']['length'];
            $isBase64 = $diagnostics['env_direct_read']['SMS_API_SECRET']['is_base64_encoded'] ?? false;
            
            if ($secretLength === 88 && $isBase64) {
                $diagnostics['recommendations'][] = 'Secret key is 88 characters and appears base64 encoded. If authentication fails, the decoded value (64 chars) should be used directly. Try updating .env with the decoded secret key.';
            } elseif ($secretLength >= 60 && $secretLength <= 70) {
                $diagnostics['recommendations'][] = 'Secret key length suggests raw format (60-70 chars). This should work directly without decoding.';
            }
        }
        
        return response()->json($diagnostics);
    }

    public function testSms(Request $request)
    {
        $this->checkSuperAdmin();
        $validated = $request->validate([
            'test_phone' => 'required|string|max:20',
            'test_message' => 'nullable|string|max:160',
            'api_key' => 'required|string|max:255',
            'api_secret' => 'required|string|max:500',
            'sender_id' => 'required|string|max:50',
        ]);

        try {
            // Use ONLY the credentials from the form/request (UI values)
            // Do NOT fall back to .env or config - use what user provided in UI
            $apiKey = trim($request->input('api_key', ''));
            $apiSecret = trim($request->input('api_secret', ''));
            $senderId = trim($request->input('sender_id', ''));
            
            // Validate that all required fields are provided
            if (empty($apiKey)) {
                return response()->json([
                    'success' => false,
                    'message' => 'API Key is required. Please enter it in the form.',
                ], 400);
            }
            
            if (empty($apiSecret)) {
                return response()->json([
                    'success' => false,
                    'message' => 'API Secret Key is required. Please enter it in the form.',
                ], 400);
            }
            
            if (empty($senderId)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sender ID is required. Please enter it in the form.',
                ], 400);
            }
            
            // Log that we're using UI-provided credentials (for debugging)
            Log::info('Testing SMS with UI-provided credentials (not from .env)', [
                'api_key_preview' => substr($apiKey, 0, 8) . '...',
                'api_key_length' => strlen($apiKey),
                'api_secret_length' => strlen($apiSecret),
                'sender_id' => $senderId,
            ]);
            
            // Create service instance with explicit credentials from UI (bypasses .env/config entirely)
            $smsService = new SmsApiService($apiKey, $apiSecret, $senderId);
            
            $message = $validated['test_message'] ?? 'Test SMS from CHIBO BRAND SMS Configuration';
            $result = $smsService->sendSMS($validated['test_phone'], $message);

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => 'SMS sent successfully! Check the phone for the test message.',
                    'data' => $result,
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'SMS sending failed: ' . ($result['message'] ?? 'Unknown error'),
                    'data' => $result,
                    'diagnostics' => [
                        'credentials_source' => 'UI form values (not from .env)',
                        'api_key_preview' => substr($apiKey, 0, 8) . '...',
                        'api_key_length' => strlen($apiKey),
                        'api_secret_length' => strlen($apiSecret),
                        'sender_id' => $senderId,
                        'hint' => 'These credentials came from the form above. Make sure they match your Beem Africa dashboard exactly.',
                    ],
                ], 400);
            }
        } catch (\Exception $e) {
            Log::error('SMS test failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Test failed: ' . $e->getMessage(),
            ], 500);
        }
    }
}



