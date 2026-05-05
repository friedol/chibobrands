<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AuditLogService
{
    /**
     * Get location information from IP address using ip-api.com
     */
    private static function getLocationFromIp(string $ip): array
    {
        // Skip localhost/private IPs
        if (in_array($ip, ['127.0.0.1', '::1', 'localhost']) || !filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            return [
                'location' => 'Local',
                'city' => null,
                'region' => null,
                'country' => null,
                'latitude' => null,
                'longitude' => null,
            ];
        }

        try {
            // Using ip-api.com free tier (no API key required)
            $response = Http::timeout(3)->get("http://ip-api.com/json/{$ip}", [
                'fields' => 'status,country,regionName,city,lat,lon'
            ]);

            if ($response->successful() && $response->json('status') === 'success') {
                $data = $response->json();
                $locationParts = array_filter([
                    $data['city'] ?? null,
                    $data['regionName'] ?? null,
                    $data['country'] ?? null,
                ]);

                return [
                    'location' => !empty($locationParts) ? implode(', ', $locationParts) : 'Unknown',
                    'city' => $data['city'] ?? null,
                    'region' => $data['regionName'] ?? null,
                    'country' => $data['country'] ?? null,
                    'latitude' => $data['lat'] ?? null,
                    'longitude' => $data['lon'] ?? null,
                ];
            }
        } catch (\Exception $e) {
            Log::warning("Failed to fetch location for IP {$ip}: " . $e->getMessage());
        }

        return [
            'location' => 'Unknown',
            'city' => null,
            'region' => null,
            'country' => null,
            'latitude' => null,
            'longitude' => null,
        ];
    }

    /**
     * Parse device information from user agent
     */
    private static function parseDeviceInfo(string $userAgent): array
    {
        $deviceType = 'desktop';
        $deviceName = 'Unknown Browser';

        // Detect device type
        if (preg_match('/(mobile|android|iphone|ipod|blackberry|iemobile|opera mini)/i', $userAgent)) {
            $deviceType = 'mobile';
        } elseif (preg_match('/(tablet|ipad|playbook|silk)/i', $userAgent)) {
            $deviceType = 'tablet';
        }

        // Extract browser name
        if (preg_match('/Chrome\/([0-9.]+)/i', $userAgent, $matches)) {
            $deviceName = 'Chrome ' . explode('.', $matches[1])[0];
        } elseif (preg_match('/Firefox\/([0-9.]+)/i', $userAgent, $matches)) {
            $deviceName = 'Firefox ' . explode('.', $matches[1])[0];
        } elseif (preg_match('/Safari\/([0-9.]+)/i', $userAgent, $matches) && !preg_match('/Chrome/i', $userAgent)) {
            $deviceName = 'Safari ' . explode('.', $matches[1])[0];
        } elseif (preg_match('/Edge\/([0-9.]+)/i', $userAgent, $matches)) {
            $deviceName = 'Edge ' . explode('.', $matches[1])[0];
        } elseif (preg_match('/Opera\/([0-9.]+)/i', $userAgent, $matches)) {
            $deviceName = 'Opera ' . explode('.', $matches[1])[0];
        }

        // Add OS info if available
        if (preg_match('/Windows NT ([0-9.]+)/i', $userAgent, $matches)) {
            $deviceName .= ' on Windows';
        } elseif (preg_match('/Mac OS X ([0-9_]+)/i', $userAgent, $matches)) {
            $deviceName .= ' on macOS';
        } elseif (preg_match('/Linux/i', $userAgent)) {
            $deviceName .= ' on Linux';
        } elseif (preg_match('/Android ([0-9.]+)/i', $userAgent, $matches)) {
            $deviceName .= ' on Android';
        } elseif (preg_match('/iPhone|iPad|iPod/i', $userAgent)) {
            $deviceName .= ' on iOS';
        }

        return [
            'device_name' => $deviceName,
            'device_type' => $deviceType,
        ];
    }

    /**
     * Log an audit event.
     */
    public static function log(string $action, string $description, $model = null, array $oldValues = null, array $newValues = null): AuditLog
    {
        $user = Auth::user();
        $ip = Request::ip();
        $userAgent = Request::userAgent() ?? '';

        // Get location from IP
        $locationData = self::getLocationFromIp($ip);

        // Parse device info from user agent
        $deviceData = self::parseDeviceInfo($userAgent);

        return AuditLog::create([
            'user_id' => $user ? $user->id : null,
            'action' => $action,
            'model_type' => $model ? get_class($model) : null,
            'model_id' => $model ? $model->id : null,
            'description' => $description,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => $ip,
            'user_agent' => $userAgent,
            'device_name' => $deviceData['device_name'],
            'device_type' => $deviceData['device_type'],
            'location' => $locationData['location'],
            'city' => $locationData['city'],
            'region' => $locationData['region'],
            'country' => $locationData['country'],
            'latitude' => $locationData['latitude'],
            'longitude' => $locationData['longitude'],
        ]);
    }

    /**
     * Log a creation event.
     */
    public static function created($model, string $description = null): AuditLog
    {
        $description = $description ?? 'Created ' . class_basename($model) . ' #' . $model->id;
        return self::log('created', $description, $model, null, $model->toArray());
    }

    /**
     * Log an update event.
     */
    public static function updated($model, array $oldValues, string $description = null): AuditLog
    {
        $description = $description ?? 'Updated ' . class_basename($model) . ' #' . $model->id;
        return self::log('updated', $description, $model, $oldValues, $model->getChanges());
    }

    /**
     * Log a deletion event.
     */
    public static function deleted($model, string $description = null): AuditLog
    {
        $description = $description ?? 'Deleted ' . class_basename($model) . ' #' . $model->id;
        return self::log('deleted', $description, $model, $model->toArray(), null);
    }

    /**
     * Log a login event.
     */
    public static function login($user): AuditLog
    {
        return self::log('login', 'User logged in', $user);
    }

    /**
     * Log a logout event.
     */
    public static function logout($user): AuditLog
    {
        return self::log('logout', 'User logged out', $user);
    }
}






