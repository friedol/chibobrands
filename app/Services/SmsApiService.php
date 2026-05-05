<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsApiService
{
    protected $apiKey;
    protected $apiSecret;
    protected $apiUrl;
    protected $senderId;

    public function __construct($apiKey = null, $apiSecret = null, $senderId = null, $apiUrl = null)
    {

        
        // API Key provided by user (Beem Africa)
        $this->apiKey = $apiKey ?? $this->getEnvOrConfig('SMS_API_KEY', 'services.sms.api_key', '2b5add88144ffb3b');
        $this->apiSecret = $apiSecret ?? $this->getEnvOrConfig('SMS_API_SECRET', 'services.sms.api_secret', '');
        
        // Beem Africa SMS API endpoint
        $this->apiUrl = $apiUrl ?? $this->getEnvOrConfig('SMS_API_URL', 'services.sms.api_url', 'https://apisms.beem.africa/v1/send');
        $this->senderId = $senderId ?? $this->getEnvOrConfig('SMS_SENDER_ID', 'services.sms.sender_id', 'CHIBOBRAND');
        
        // Clean and trim all values to remove any whitespace/newlines
        $this->apiKey = trim($this->apiKey ?? '');
        $this->apiSecret = trim($this->apiSecret ?? '');
        $this->senderId = trim($this->senderId ?? '');
        
        // Remove any hidden characters (non-printable characters)
        $this->apiKey = preg_replace('/[\x00-\x1F\x7F]/u', '', $this->apiKey);
        $this->apiSecret = preg_replace('/[\x00-\x1F\x7F]/u', '', $this->apiSecret);
        $this->senderId = preg_replace('/[\x00-\x1F\x7F]/u', '', $this->senderId);
        
        // Log diagnostic info (without exposing full secrets)
        Log::info('SmsApiService initialized', [
            'api_key_preview' => substr($this->apiKey, 0, 8) . '...',
            'api_key_length' => strlen($this->apiKey),
            'api_secret_set' => !empty($this->apiSecret),
            'api_secret_length' => strlen($this->apiSecret),
            'sender_id' => $this->senderId,
            'api_url' => $this->apiUrl,
            'env_sms_api_key_set' => !empty(env('SMS_API_KEY')),
            'env_sms_api_secret_set' => !empty(env('SMS_API_SECRET')),
            'config_cached' => app()->configurationIsCached(),
            'app_env' => app()->environment(),
            'env_file_exists' => file_exists(base_path('.env')),
            'env_file_readable' => file_exists(base_path('.env')) ? is_readable(base_path('.env')) : false,
        ]);
        
        // Log warning if secret key is missing
        if (empty($this->apiSecret)) {
            Log::warning('Beem Africa SMS API Secret Key not configured. SMS sending may fail. Please set SMS_API_SECRET in .env file.');
        }
        
        // Log warning if API key is missing
        if (empty($this->apiKey)) {
            Log::warning('Beem Africa SMS API Key not configured. SMS sending may fail. Please set SMS_API_KEY in .env file.');
        }
    }
    
    /**
     * Get value from environment or config, with fallback
     * Prioritizes env() to avoid config cache issues
     * In production, reads directly from .env file to bypass config cache
     */
    private function getEnvOrConfig($envKey, $configKey, $default = null)
    {
        // Force reload .env file if in production or if config is cached
        // This helps when config is cached but .env changed
        if (app()->environment('production') || app()->configurationIsCached()) {
            // Try to read directly from .env file
            $envFile = base_path('.env');
            if (file_exists($envFile) && is_readable($envFile)) {
                $envContent = file_get_contents($envFile);
                
                // Pattern to match: KEY="value" or KEY='value' or KEY=value (handles multiline values)
                // More robust pattern that handles quoted values properly
                $patterns = [
                    "/^{$envKey}=\"([^\"]*)\"/m",  // Double quoted
                    "/^{$envKey}='([^']*)'/m",      // Single quoted
                    "/^{$envKey}=([^\\r\\n]*?)(?=\\r?\\n|$)/m", // Unquoted (everything until newline or end)
                ];
                
                foreach ($patterns as $pattern) {
                    if (preg_match($pattern, $envContent, $matches)) {
                        $envValue = trim($matches[1] ?? '');
                        if (!empty($envValue)) {
                            // Remove surrounding quotes if still present
                            $envValue = trim($envValue, '"\'');
                            
                            Log::debug("Read {$envKey} directly from .env file", [
                                'preview' => substr($envValue, 0, 8) . '...',
                                'length' => strlen($envValue),
                                'method' => 'direct_file_read',
                            ]);
                            return $envValue;
                        }
                    }
                }
            } else {
                Log::warning("Could not read .env file directly", [
                    'env_file' => $envFile,
                    'exists' => file_exists($envFile),
                    'readable' => file_exists($envFile) ? is_readable($envFile) : false,
                ]);
            }
        }
        
        // Fallback 1: Try env() (works when config is not cached)
        $value = env($envKey);
        if ($value !== null && $value !== '') {
            Log::debug("Read {$envKey} from env()", [
                'preview' => substr($value, 0, 8) . '...',
                'method' => 'env_helper',
            ]);
            return trim($value, '"\'');
        }
        
        // Fallback 2: Try config() (may be cached but still works sometimes)
        $value = config($configKey);
        if ($value !== null && $value !== '') {
            Log::debug("Read {$envKey} from config()", [
                'preview' => substr($value, 0, 8) . '...',
                'method' => 'config_helper',
            ]);
            return is_string($value) ? trim($value, '"\'') : $value;
        }
        
        // Fallback 3: Return default
        Log::debug("Using default value for {$envKey}", [
            'method' => 'default',
        ]);
        return $default;
    }

    /**
     * Send SMS message
     *
     * @param string 
     * @param string 
     * @return array 
     */
    public function sendSMS($to, $message)
    {
        try {
            // Format phone number
            $to = $this->formatPhoneNumber($to);
            
            // Try different API endpoint patterns common in Tanzania
            $providers = [
                [
                    'url' => 'https://api.mambosms.com/v1/sms/send',
                    'method' => 'post',
                    'params' => [
                        'api_key' => $this->apiKey,
                        'to' => $to,
                        'message' => $message,
                        'sender_id' => $this->senderId,
                    ]
                ],
                [
                    'url' => 'https://api.darsms.co.tz/api/sms/send',
                    'method' => 'post',
                    'params' => [
                        'apikey' => $this->apiKey,
                        'mobile' => $to,
                        'message' => $message,
                        'sender' => $this->senderId,
                    ]
                ],
                [
                    'url' => 'https://app.nextsms.co.tz/api/v1/sms',
                    'method' => 'post',
                    'headers' => [
                        'Authorization' => 'Bearer ' . $this->apiKey,
                    ],
                    'params' => [
                        'mobile' => $to,
                        'message' => $message,
                        'sender_id' => $this->senderId,
                    ]
                ],
            ];

            // Try Beem Africa API format first (primary)
            $lastResponse = null;
            
            // Method 1: Beem Africa API (Primary method)
            try {
                // Beem Africa requires API Key and Secret Key for Basic Auth
                // Format: base64(api_key:secret_key)
                $secretKey = $this->apiSecret ?? '';
                
                if (empty($secretKey)) {
                    Log::error('Beem Africa API Secret Key is missing');
                    throw new \Exception('API Secret Key is required for Beem Africa');
                }
                
                // Prepare API key (clean and trim - already done in constructor, but do it again for safety)
                $apiKeyToUse = trim($this->apiKey);
                $secretKeyRaw = trim($secretKey);
                
                // Remove any hidden characters that might cause issues
                $apiKeyToUse = preg_replace('/[\x00-\x1F\x7F]/u', '', $apiKeyToUse);
                $secretKeyRaw = preg_replace('/[\x00-\x1F\x7F]/u', '', $secretKeyRaw);
                
                // Remove any quotes that might have been included
                $apiKeyToUse = trim($apiKeyToUse, '"\'');
                $secretKeyRaw = trim($secretKeyRaw, '"\'');
                
                // Validate key lengths
                if (empty($apiKeyToUse) || strlen($apiKeyToUse) < 10) {
                    Log::error('SMS API Key validation failed', [
                        'key_preview' => substr($apiKeyToUse, 0, 8) . '...',
                        'key_length' => strlen($apiKeyToUse),
                        'env_value_preview' => substr(env('SMS_API_KEY') ?? '', 0, 8) . '...',
                    ]);
                    throw new \Exception('API Key appears to be invalid or too short. Please check SMS_API_KEY in .env file.');
                }
                
                if (empty($secretKeyRaw) || strlen($secretKeyRaw) < 10) {
                    Log::error('SMS API Secret Key validation failed', [
                        'secret_length' => strlen($secretKeyRaw),
                        'env_secret_set' => !empty(env('SMS_API_SECRET')),
                        'config_secret_set' => !empty(config('services.sms.api_secret')),
                    ]);
                    throw new \Exception('API Secret Key appears to be invalid or too short. Please check SMS_API_SECRET in .env file.');
                }
                
                // Try multiple secret key formats since credentials might be stored differently
                // Beem Africa typically provides secret keys as raw hexadecimal strings (64 chars)
                // but they're often stored base64 encoded in .env files
                $secretKeyOptions = [];
                
                // Option 1: Use secret as-is (raw secret key - try this FIRST if it looks like raw format)
                // Beem Africa raw secrets are typically 64-character hex strings
                $isLikelyRawSecret = strlen($secretKeyRaw) >= 60 && strlen($secretKeyRaw) <= 70 && 
                                    ctype_xdigit(str_replace(['-', '_'], '', $secretKeyRaw));
                
                // If it looks like a raw secret (64 hex chars), try it first
                if ($isLikelyRawSecret) {
                    $secretKeyOptions[] = [
                        'format' => 'raw_likely',
                        'value' => $secretKeyRaw,
                        'description' => 'Secret key used as-is (appears to be raw format)',
                        'priority' => 1
                    ];
                } else {
                    // Otherwise try raw as default
                    $secretKeyOptions[] = [
                        'format' => 'raw',
                        'value' => $secretKeyRaw,
                        'description' => 'Secret key used as-is (raw format)',
                        'priority' => 3
                    ];
                }
                
                // Option 2: Decode if it's base64 encoded (most common when stored in .env)
                // Beem Africa secret keys are often stored as base64 in .env files
                // Base64 encoded 64-char hex = 88 chars, which matches your secret length
                $decodedSecret = @base64_decode($secretKeyRaw, true);
                if ($decodedSecret !== false && 
                    $decodedSecret !== '' &&
                    strlen($decodedSecret) >= 10 &&
                    // Only add if the decoded value is different and looks valid
                    ($decodedSecret !== $secretKeyRaw)) {
                    // If decoded result looks like a raw secret (64 hex chars), prioritize it
                    $decodedIsLikelyRaw = strlen($decodedSecret) >= 60 && strlen($decodedSecret) <= 70;
                    
                    $secretKeyOptions[] = [
                        'format' => 'decoded_base64',
                        'value' => $decodedSecret,
                        'description' => 'Secret key decoded from base64' . ($decodedIsLikelyRaw ? ' (looks like raw secret)' : ''),
                        'priority' => $decodedIsLikelyRaw ? 2 : 4
                    ];
                }
                
                // Option 3: Try URL decoding (in case it was URL encoded)
                $urlDecoded = urldecode($secretKeyRaw);
                if ($urlDecoded !== $secretKeyRaw && strlen($urlDecoded) >= 10) {
                    $secretKeyOptions[] = [
                        'format' => 'url_decoded',
                        'value' => $urlDecoded,
                        'description' => 'Secret key URL decoded',
                        'priority' => 5
                    ];
                }
                
                // Option 4: Try both base64 decode AND URL decode
                if (isset($decodedSecret) && $decodedSecret !== false && $decodedSecret !== '') {
                    $doubleDecoded = urldecode($decodedSecret);
                    if ($doubleDecoded !== $decodedSecret && 
                        $doubleDecoded !== $secretKeyRaw && 
                        strlen($doubleDecoded) >= 10) {
                        $secretKeyOptions[] = [
                            'format' => 'base64_then_url_decoded',
                            'value' => $doubleDecoded,
                            'description' => 'Secret key base64 decoded then URL decoded',
                            'priority' => 6
                        ];
                    }
                }
                
                // Sort by priority (lower priority number = try first)
                usort($secretKeyOptions, function($a, $b) {
                    return ($a['priority'] ?? 999) <=> ($b['priority'] ?? 999);
                });
                
                // Log all formats we'll try (in priority order)
                Log::info('Prepared secret key formats for authentication (ordered by priority)', [
                    'formats_with_priority' => array_map(function($opt) { 
                        return [
                            'format' => $opt['format'], 
                            'length' => strlen($opt['value']),
                            'priority' => $opt['priority'] ?? 999,
                            'description' => $opt['description'] ?? '',
                        ]; 
                    }, $secretKeyOptions),
                    'total_formats' => count($secretKeyOptions),
                ]);
                
                // Prepare request data once (per Beem Africa API spec)
                $requestData = [
                    'source_addr' => $this->senderId,
                    'schedule_time' => '',
                    'encoding' => 0,
                    'message' => $message,
                    'recipients' => [
                        [
                            'recipient_id' => '1', // Must be string per Beem Africa API
                            'dest_addr' => $to,
                        ]
                    ],
                ];
                
                // Log request payload for debugging (without sensitive data)
                Log::debug('Beem Africa API request payload prepared', [
                    'source_addr' => $this->senderId,
                    'message_length' => strlen($message),
                    'recipient_count' => count($requestData['recipients']),
                    'dest_addr_preview' => substr($to, 0, 5) . '...',
                ]);
                
                Log::info('Beem Africa API authentication attempt - trying multiple formats', [
                    'api_key_preview' => substr($apiKeyToUse, 0, 8) . '...',
                    'api_key_length' => strlen($apiKeyToUse),
                    'secret_formats_to_try' => count($secretKeyOptions),
                    'sender_id' => $this->senderId,
                ]);
                
                // Try each secret key format
                foreach ($secretKeyOptions as $index => $secretOption) {
                    $secretKeyToUse = $secretOption['value'];
                    $formatName = $secretOption['format'];
                    
                    // Create credentials: base64_encode(api_key:secret_key)
                    // Ensure no whitespace in the concatenation
                    $credentialsString = trim($apiKeyToUse) . ':' . trim($secretKeyToUse);
                    $credentials = base64_encode($credentialsString);
                    
                    // Verify the base64 encoding worked correctly
                    $decodedCheck = base64_decode($credentials, true);
                    if ($decodedCheck === false || $decodedCheck !== $credentialsString) {
                        Log::warning("Base64 encoding verification failed for format: {$formatName}", [
                            'format' => $formatName,
                        ]);
                    }
                    
                    Log::info("Trying authentication with format: {$formatName}", [
                        'format' => $formatName,
                        'api_key_length' => strlen($apiKeyToUse),
                        'api_key_preview' => substr($apiKeyToUse, 0, 8) . '...',
                        'secret_key_length' => strlen($secretKeyToUse),
                        'secret_key_preview' => substr($secretKeyToUse, 0, 8) . '...',
                        'credentials_preview' => substr($credentials, 0, 25) . '...',
                        'credentials_length' => strlen($credentials),
                    ]);
                    
                    // Make the HTTP request with proper headers
                    $response = Http::timeout(30)
                        ->withHeaders([
                            'Authorization' => 'Basic ' . $credentials,
                            'Content-Type' => 'application/json',
                            'Accept' => 'application/json',
                        ])
                        ->post('https://apisms.beem.africa/v1/send', $requestData);
                
                    $lastResponse = $response;
                    
                    if ($response->successful()) {
                        $responseData = $response->json();
                        
                        Log::info('SMS sent successfully via Beem Africa API', [
                            'to' => $to,
                            'http_status' => $response->status(),
                            'response' => $responseData,
                            'format_used' => $formatName,
                        ]);
                        
                        // Check if response indicates success
                        $isSuccess = $response->status() === 200;
                        $isSuccess = $isSuccess && !isset($responseData['error']);
                        
                        if ($isSuccess) {
                            return [
                                'success' => true,
                                'message' => 'SMS sent successfully',
                                'data' => $responseData,
                            ];
                        } else {
                            Log::warning('Beem Africa API returned error in response', [
                                'to' => $to,
                                'response' => $responseData,
                                'format_used' => $formatName,
                            ]);
                        }
                    } else {
                        $errorData = $response->json();
                        $statusCode = $response->status();
                        
                        // If 401, try next format (unless this was the last one)
                        if ($statusCode === 401 && $index < count($secretKeyOptions) - 1) {
                            Log::warning("Authentication failed with format {$formatName}, trying next format", [
                                'format' => $formatName,
                                'status' => $statusCode,
                            ]);
                            continue; // Try next format
                        }
                        
                        // If not 401, or if this was the last format, return error
                        $errorMessage = $errorData['message'] ?? $errorData['error'] ?? 'Unknown error';
                        
                        if ($statusCode === 401) {
                            // Try to get more detailed error info
                            $errorCode = $errorData['code'] ?? null;
                            $errorMsg = $errorData['message'] ?? 'Invalid Authentication Parameters';
                            
                            $errorMessage = 'Invalid Authentication Parameters after trying all formats. ' .
                                          'Error Code: ' . ($errorCode ?? 'N/A') . '. ' .
                                          'Please verify in Beem Africa dashboard: ' .
                                          '1) API Key is exactly correct (no extra spaces, quotes, or newlines), ' .
                                          '2) Secret Key is exactly correct - try copying directly from dashboard, ' .
                                          '3) Both are active and have SMS credits, ' .
                                          '4) Sender ID is approved and matches exactly (case-sensitive). ' .
                                          'Check Laravel logs for detailed authentication attempts. ' .
                                          'TROUBLESHOOTING: In your .env file, ensure values are not wrapped in quotes if they contain quotes: SMS_API_KEY=yourkey (not SMS_API_KEY="yourkey"). ' .
                                          'Also verify the secret key format - it may need to be decoded from base64.';
                            
                            // Read actual values from .env for debugging (without exposing full secrets)
                            $envFile = base_path('.env');
                            $envDebug = [];
                            if (file_exists($envFile)) {
                                $envContent = file_get_contents($envFile);
                                foreach (['SMS_API_KEY', 'SMS_API_SECRET', 'SMS_SENDER_ID'] as $key) {
                                    if (preg_match("/^{$key}=[\"']?([^\"'\n\r]*)[\"']?/m", $envContent, $matches)) {
                                        $val = trim($matches[1] ?? '');
                                        $envDebug[$key] = [
                                            'has_quotes_in_file' => preg_match("/^{$key}=[\"']/m", $envContent),
                                            'length' => strlen($val),
                                            'preview' => substr($val, 0, 8) . '...',
                                            'ends_with_newline' => substr($val, -1) === "\n" || substr($val, -1) === "\r",
                                        ];
                                    }
                                }
                            }
                            
                            Log::error('Beem Africa API authentication failed (401) - all formats tried', [
                                'to' => $to,
                                'status' => $statusCode,
                                'error_code' => $errorCode,
                                'error_message' => $errorMsg,
                                'response' => $errorData,
                                'api_key_preview' => substr($apiKeyToUse, 0, 8) . '...',
                                'api_key_length' => strlen($apiKeyToUse),
                                'api_key_has_quotes' => strpos($apiKeyToUse, '"') !== false || strpos($apiKeyToUse, "'") !== false,
                                'secret_formats_tried' => array_column($secretKeyOptions, 'format'),
                                'secret_lengths' => array_map(function($opt) { 
                                    return ['format' => $opt['format'], 'length' => strlen($opt['value'])]; 
                                }, $secretKeyOptions),
                                'sender_id' => $this->senderId,
                                'sender_id_length' => strlen($this->senderId),
                                'sender_id_has_quotes' => strpos($this->senderId, '"') !== false || strpos($this->senderId, "'") !== false,
                                'config_cached' => app()->configurationIsCached(),
                                'env_debug' => $envDebug,
                                'hint' => 'Verify credentials match Beem Africa dashboard EXACTLY. Secret key of 88 chars suggests base64 - try decoding it. Also ensure Sender ID matches approved ID exactly (case-sensitive).',
                            ]);
                        } else {
                            Log::warning('Beem Africa API returned non-success status', [
                                'to' => $to,
                                'status' => $statusCode,
                                'response' => $errorData,
                                'error_message' => $errorMessage,
                            ]);
                        }
                        
                        // Return specific error message
                        return [
                            'success' => false,
                            'message' => 'SMS sending failed: ' . $errorMessage,
                            'status' => $statusCode,
                            'error_data' => $errorData,
                        ];
                    }
                } // End foreach secretKeyOptions
            } catch (\Exception $e) {
                Log::error('Beem Africa API exception', [
                    'to' => $to,
                    'error' => $e->getMessage(),
                ]);
            }

            // If all methods failed, log and return error
            $errorData = $lastResponse ? $lastResponse->json() : null;
            
            Log::error('SMS sending failed - all methods tried', [
                'to' => $to,
                'status' => $lastResponse ? $lastResponse->status() : 'No response',
                'error' => $errorData,
            ]);

            return [
                'success' => false,
                'message' => 'SMS sending failed: ' . ($errorData['message'] ?? 'All API methods failed. Please check API key and endpoint configuration.'),
                'status' => $lastResponse ? $lastResponse->status() : null,
            ];

        } catch (\Exception $e) {
            Log::error('SMS sending exception', [
                'to' => $to,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => 'SMS sending failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Format phone number for Tanzania (+255)
     *
     * @param string $phone Phone number to format
     * @return string Formatted phone number
     */
    public function formatPhoneNumber($phone)
    {
        // Remove any non-numeric characters except +
        $phone = preg_replace('/[^0-9+]/', '', $phone);
        
        // If it starts with 0, replace with 255
        if (str_starts_with($phone, '0')) {
            $phone = '255' . substr($phone, 1);
        }
        
        // If it starts with +255, remove +
        if (str_starts_with($phone, '+255')) {
            $phone = '255' . substr($phone, 4);
        }
        
        // If it doesn't start with 255, add it
        if (!str_starts_with($phone, '255')) {
            $phone = '255' . $phone;
        }
        
        return $phone;
    }

    /**
     * Send task notification to customer
     *
     * @param \App\Models\Customer $customer
     * @param \App\Models\DesignTask $task
     * @param string $status Status change message
     * @return array
     */
    public function sendTaskNotification($customer, $task, $status = 'created')
    {
        // Check if customer and phone exist
        if (!$customer) {
            Log::warning('Cannot send SMS: Customer is null', [
                'task_id' => $task->id ?? null,
            ]);
            return [
                'success' => false,
                'message' => 'Customer is null',
            ];
        }

        if (!$customer->phone || trim($customer->phone) === '') {
            Log::warning('Cannot send SMS: Customer phone number not available', [
                'customer_id' => $customer->id,
                'customer_name' => $customer->name,
                'task_id' => $task->id ?? null,
            ]);
            return [
                'success' => false,
                'message' => 'Customer phone number not available',
            ];
        }

        try {
            // Create short message based on status (SMS length limit: ~160 characters)
            $taskTitle = strlen($task->title) > 30 ? substr($task->title, 0, 27) . '...' : $task->title;
            $customerName = strlen($customer->name) > 20 ? substr($customer->name, 0, 17) . '...' : $customer->name;
            
            // Determine contact number
            $sellerContact = "0655392319"; // Default Chibo
            if ($task->saler_id) {
                $saler = \App\Models\User::find($task->saler_id);
                if ($saler && $saler->phone) {
                    $sellerContact = $saler->phone;
                }
            } else if ($task->receptionist_id) {
                 $receptionist = \App\Models\User::find($task->receptionist_id);
                 if ($receptionist && $receptionist->phone) {
                     $sellerContact = $receptionist->phone;
                 }
            }

            $messages = [
                'created' => "Hello {$customerName}, task '{$taskTitle}' created. We'll update you soon. Contact: {$sellerContact}. - CHIBOBRAND",
                'assigned' => "Hello {$customerName}, task '{$taskTitle}' assigned to designer. Contact: {$sellerContact}. - CHIBOBRAND",
                'in_progress' => "Hello {$customerName}, work on '{$taskTitle}' started. Contact: {$sellerContact}. - CHIBOBRAND",
                'completed' => "Hello {$customerName}, task '{$taskTitle}' is complete! Contact: {$sellerContact}. - CHIBOBRAND",
                'super_completed' => "Hello {$customerName}, '{$taskTitle}' is ready! Code: {$task->pickup_code}. Total: " . number_format($task->price, 0) . " TZS, Balance: " . number_format($task->balance, 0) . " TZS. Contact: {$sellerContact} - CHIBOBRAND",
                'status_changed' => "Hello {$customerName}, '{$taskTitle}' status: {$task->status_label}. Contact: {$sellerContact}. - CHIBOBRAND",
                'delivery_assigned' => "Hello {$customerName}, '{$taskTitle}' is out for delivery. Contact: {$sellerContact}. - CHIBOBRAND",
                'delivered' => "Hello {$customerName}, '{$taskTitle}' has been delivered. Thank you! Contact: {$sellerContact}. - CHIBOBRAND",
            ];

            $message = $messages[$status] ?? $messages['status_changed'];

            Log::info('Preparing to send SMS notification', [
                'customer_id' => $customer->id,
                'customer_phone' => $customer->phone,
                'task_id' => $task->id ?? null,
                'status' => $status,
                'message' => $message,
            ]);

            $result = $this->sendSMS($customer->phone, $message);
            
            Log::info('SMS notification result', [
                'customer_id' => $customer->id,
                'customer_phone' => $customer->phone,
                'task_id' => $task->id ?? null,
                'success' => $result['success'] ?? false,
                'result_message' => $result['message'] ?? 'No message',
            ]);

            return $result;
        } catch (\Exception $e) {
            Log::error('Exception in sendTaskNotification', [
                'customer_id' => $customer->id,
                'task_id' => $task->id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return [
                'success' => false,
                'message' => 'Exception: ' . $e->getMessage(),
            ];
        }
    }
    /**
     * Send receipt SMS for an Order
     */
    public function sendOrderReceipt($order)
    {
        if (!$order->user || !$order->user->phone) {
            return ['success' => false, 'message' => 'No customer phone'];
        }

        try {
            // Format:
            // Hello!! [Name]
            // [Order No] imepokelewa CHIBO BRANDS , [Time]
            // [Products]
            // Umelipia Tsh[Paid], kiasi kilichobaki [Balance].
            // Order yako itakamilika [Date] Saa 12:00

            $name = $order->user->name;
            $ref = $order->order_code;
            $time = $order->created_at->timezone('Africa/Dar_es_Salaam')->format('H:i');
            
            // Products list (limit length)
            $products = collect($order->items)->map(function($item) {
                return $item->product_name ?? ($item->product->name ?? 'Item');
            })->implode(', ');
            if (strlen($products) > 30) $products = substr($products, 0, 27) . '...';

            // Payment calculations
            $total = $order->total_amount;
            // Use stored amount_paid/balance if they exist (likely from POS), otherwise fallback to payment_status
            $paid = $order->amount_paid ?? ($order->payment_status === 'paid' ? $total : 0);
            $balance = $order->balance ?? ($total - $paid);

            // Estimated completion (default 3 days if not set)
            $completionDate = $order->created_at->copy()->timezone('Africa/Dar_es_Salaam')->addDays(3)->format('d F Y');

            $message = "Hello!! {$name}\n" .
                      "{$ref} imepokelewa CHIBO BRANDS , {$time}\n" .
                      "{$products}\n" .
                      "Umelipia Tsh" . number_format($paid) . ", kiasi kilichobaki " . number_format($balance) . ".\n" .
                      "Order yako itakamilika {$completionDate} Saa 12:00";

            return $this->sendSMS($order->user->phone, $message);
        } catch (\Exception $e) {
            Log::error('Error sending order receipt SMS: ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Send receipt SMS for a Design Task
     */
    public function sendTaskReceipt($task)
    {
        if (!$task->customer || !$task->customer->phone) {
            return ['success' => false, 'message' => 'No customer phone'];
        }

        try {
            $name = $task->customer->name;
            $ref = $task->task_code ?? ("Task #" . $task->id);
            $time = $task->created_at->timezone('Africa/Dar_es_Salaam')->format('H:i');
            
            // Task title as product description
            $products = $task->title;
            if (strlen($products) > 30) $products = substr($products, 0, 27) . '...';

            // Payment calculations
            $total = ($task->requires_receipt ? $task->price * 1.18 : $task->price);
            $paid = $task->amount_paid;
            $balance = $task->balance;

            // Deadline
            $completionDate = $task->deadline ? $task->deadline->timezone('Africa/Dar_es_Salaam')->format('d F Y') : $task->created_at->copy()->timezone('Africa/Dar_es_Salaam')->addDays(3)->format('d F Y');
            $completionTime = $task->deadline ? $task->deadline->timezone('Africa/Dar_es_Salaam')->format('H:i') : '12:00';

            // Seller Contact
            $sellerContact = "0655392319"; // Default Chibo
            if ($task->saler_id) {
                $saler = \App\Models\User::find($task->saler_id);
                if ($saler && $saler->phone) {
                    $sellerContact = $saler->phone;
                }
            } else if ($task->receptionist_id) {
                 $receptionist = \App\Models\User::find($task->receptionist_id);
                 if ($receptionist && $receptionist->phone) {
                     $sellerContact = $receptionist->phone;
                 }
            }

            $message = "Hello!! {$name}\n" .
                      "{$ref} imepokelewa CHIBO BRANDS , {$time}\n" .
                      "{$products}\n" .
                      "Umelipia Tsh" . number_format($paid) . ", kiasi kilichobaki " . number_format($balance) . ".\n" .
                      "Order yako itakamilika {$completionDate} Saa {$completionTime}\n" .
                      "Tujulishe Kwa Namba {$sellerContact}";

            return $this->sendSMS($task->customer->phone, $message);
        } catch (\Exception $e) {
            Log::error('Error sending task receipt SMS: ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Send batch receipt SMS for multiple Design Tasks
     */
    public function sendBatchTaskReceipt($tasks)
    {
        if (empty($tasks)) return ['success' => false, 'message' => 'No tasks provided'];
        
        $firstTask = $tasks[0];
        if (!$firstTask->customer || !$firstTask->customer->phone) {
            return ['success' => false, 'message' => 'No customer phone'];
        }

        try {
            $name = $firstTask->customer->name;
            $time = $firstTask->created_at->timezone('Africa/Dar_es_Salaam')->format('H:i');
            
            // Build task list with individual deadlines
            $taskList = [];
            $totalPaid = 0;
            $totalBalance = 0;

            foreach ($tasks as $index => $task) {
                $totalPaid += $task->amount_paid;
                $totalBalance += $task->balance;
                
                // Format individual deadline
                $taskDeadline = '';
                if ($task->deadline) {
                    $deadlineDate = $task->deadline->timezone('Africa/Dar_es_Salaam')->format('d M');
                    $deadlineTime = $task->deadline->timezone('Africa/Dar_es_Salaam')->format('H:i');
                    // Format: itakamilika 12 Jan 14:00
                    $taskDeadline = " itakamilika {$deadlineDate} {$deadlineTime}";
                } else {
                    // Default 3 days from creation
                    $defaultDeadline = $task->created_at->copy()->timezone('Africa/Dar_es_Salaam')->addDays(3);
                    $deadlineDate = $defaultDeadline->format('d M');
                    // Default time 12:00
                    $taskDeadline = " itakamilika {$deadlineDate} 12:00";
                }
                
                // Truncate long titles
                $title = $task->title;
                if (strlen($title) > 35) {
                    $title = substr($title, 0, 32) . '...';
                }
                
                $taskList[] = ($index + 1) . ". {$title}{$taskDeadline}";
            }

            $tasksText = implode("\n", $taskList);

            // Seller Contact
            $sellerContact = "0655392319"; // Default Chibo
            if ($firstTask->saler_id) {
                $saler = \App\Models\User::find($firstTask->saler_id);
                if ($saler && $saler->phone) {
                    $sellerContact = $saler->phone;
                }
            } else if ($firstTask->receptionist_id) {
                $receptionist = \App\Models\User::find($firstTask->receptionist_id);
                if ($receptionist && $receptionist->phone) {
                    $sellerContact = $receptionist->phone;
                }
            }

            $taskCount = count($tasks);
            $orderWord = $taskCount > 1 ? "Orders" : "Order";

            $message = "Hello!! {$name}\n" .
                      "{$orderWord} imepokelewa CHIBO BRANDS, {$time}\n\n" .
                      "{$tasksText}\n\n" .
                      "Umelipia Tsh" . number_format($totalPaid) . ", kiasi kilichobaki " . number_format($totalBalance) . ".\n" .
                      "Tujulishe Kwa Namba {$sellerContact}";

            return $this->sendSMS($firstTask->customer->phone, $message);
        } catch (\Exception $e) {
            Log::error('Error sending batch task receipt SMS: ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    /**
     * Send completion SMS for an Order
     */
    public function sendOrderCompletion($order)
    {
        if (!$order->user || !$order->user->phone) {
            return ['success' => false, 'message' => 'No customer phone'];
        }

        try {
            // Format:
            // Hello!! [Client Name]
            // CHIBO BRANDS tunakutaarifu kuwa order ( [Order No] ) yako imekamilika
            // Umelipia [Paid Price] , kiasi kilichobaki [Remaining Price]
            // Tafadhali tujulishe kama unakuja kuchukua au tukufanyie delivery kwa Namba [Seller Contact]

            $name = $order->user->name;
            $ref = $order->order_code;
            
            // Payment calculations
            $total = $order->total_amount;
            $paid = $order->payment_status === 'paid' ? $total : 0;
            $balance = $total - $paid;

            // Seller Contact
            // Use Saler's phone if assigned, otherwise company default?
            $sellerContact = "0655392319"; // Default Chibo
            if ($order->saler_id) {
                $saler = \App\Models\User::find($order->saler_id);
                if ($saler && $saler->phone) {
                    $sellerContact = $saler->phone;
                }
            }
            // Format contact (remove +255 if present for readability?) User asked for "Namba [Seller Contact]"
            
            $message = "Hello!! {$name}\n" .
                      "CHIBO BRANDS tunakutaarifu kuwa order ( {$ref} ) yako imekamilika\n" .
                      "Umelipia Tsh" . number_format($paid) . " , kiasi kilichobaki Tsh" . number_format($balance) . "\n" .
                      "Tafadhali tujulishe kama unakuja kuchukua au tukufanyie delivery kwa Namba {$sellerContact}";

            return $this->sendSMS($order->user->phone, $message);
        } catch (\Exception $e) {
            Log::error('Error sending order completion SMS: ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Send completion SMS for a Design Task (Super Completed)
     */
    public function sendTaskCompletion($task)
    {
        if (!$task->customer || !$task->customer->phone) {
            return ['success' => false, 'message' => 'No customer phone'];
        }

        try {
            $name = $task->customer->name;
            $ref = $task->task_code ?? ("#" . $task->id);
            
            // Payment calculations
            $total = ($task->requires_receipt ? $task->price * 1.18 : $task->price);
            $paid = $task->amount_paid;
            $balance = $task->balance;

            // Seller Contact
            $sellerContact = "0655392319"; // Default Chibo
            if ($task->saler_id) {
                $saler = \App\Models\User::find($task->saler_id);
                if ($saler && $saler->phone) {
                    $sellerContact = $saler->phone;
                }
            } else if ($task->receptionist_id) {
                 $receptionist = \App\Models\User::find($task->receptionist_id);
                 if ($receptionist && $receptionist->phone) {
                     $sellerContact = $receptionist->phone;
                 }
            }

            $message = "Hello!! {$name}\n" .
                      "CHIBO BRANDS tunakutaarifu kuwa order ( {$ref} ) yako imekamilika\n" .
                      "Verification Code: {$task->pickup_code}\n" .
                      "Umelipia Tsh" . number_format($paid) . " , kiasi kilichobaki Tsh" . number_format($balance) . "\n" .
                      "Tafadhali tujulishe kama unakuja kuchukua au tukufanyie delivery.\n" .
                      "Tujulishe Kwa Namba {$sellerContact}";

            return $this->sendSMS($task->customer->phone, $message);
        } catch (\Exception $e) {
            Log::error('Error sending task completion SMS: ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
