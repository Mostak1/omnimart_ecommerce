<?php

namespace App\Helpers;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Request;

class FacebookCapiHelper
{
    /**
     * Send event to Facebook Conversion API.
     *
     * @param string $eventName
     * @param array $userData
     * @param array $customData
     * @param string|null $eventId
     * @return void
     */
    public static function sendEvent($eventName, $userData = [], $customData = [], $eventId = null)
    {
        $setting = Setting::find(1);

        if (!$setting->is_facebook_capi || !$setting->facebook_access_token || !$setting->facebook_pixel_id) {
            return;
        }

        $url = "https://graph.facebook.com/v17.0/{$setting->facebook_pixel_id}/events?access_token={$setting->facebook_access_token}";

        $data = [
            'data' => [
                [
                    'event_name' => $eventName,
                    'event_time' => time(),
                    'event_source_url' => Request::fullUrl(),
                    'action_source' => 'website',
                    'user_data' => self::formatUserData($userData),
                    'custom_data' => $customData,
                ]
            ]
        ];

        if ($eventId) {
            $data['data'][0]['event_id'] = $eventId;
        }

        if ($setting->facebook_test_code) {
            $data['test_event_code'] = $setting->facebook_test_code;
        }

        try {
            Http::post($url, $data);
        } catch (\Exception $e) {
            // Silently fail or log error
            \Log::error('Facebook CAPI Error: ' . $e->getMessage());
        }
    }

    /**
     * Format and hash user data for Facebook.
     *
     * @param array $userData
     * @return array
     */
    private static function formatUserData($userData)
    {
        $formatted = [];

        // Basic user data from Auth if available
        if (auth()->check()) {
            $user = auth()->user();
            $formatted['em'] = [hash('sha256', strtolower(trim($user->email)))];
            if ($user->phone) {
                $formatted['ph'] = [hash('sha256', self::cleanPhone($user->phone))];
            }
            $formatted['fn'] = [hash('sha256', strtolower(trim($user->first_name)))];
            $formatted['ln'] = [hash('sha256', strtolower(trim($user->last_name)))];
        }

        // Merge with provided user data
        foreach ($userData as $key => $value) {
            if ($value) {
                switch ($key) {
                    case 'em':
                    case 'ph':
                    case 'fn':
                    case 'ln':
                    case 'ct':
                    case 'st':
                    case 'zp':
                    case 'country':
                        $formatted[$key] = [hash('sha256', strtolower(trim($value)))];
                        break;
                    default:
                        $formatted[$key] = $value;
                }
            }
        }

        // Client IP and User Agent are important for matching
        $formatted['client_ip_address'] = Request::ip();
        $formatted['client_user_agent'] = Request::header('User-Agent');

        // External ID for better matching
        if (auth()->check()) {
            $formatted['external_id'] = [hash('sha256', auth()->id())];
        }

        return $formatted;
    }

    /**
     * Clean phone number for hashing.
     *
     * @param string $phone
     * @return string
     */
    private static function cleanPhone($phone)
    {
        return preg_replace('/[^0-9]/', '', $phone);
    }
}
