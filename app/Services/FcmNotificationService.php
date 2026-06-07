<?php

namespace App\Services;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Illuminate\Support\Facades\Log;

class FcmNotificationService
{
    protected $messaging;

    public function __construct()
    {
        try {
            $credentialsPath = base_path('firebase-credentials.json');
            
            if (!file_exists($credentialsPath)) {
                Log::warning('FCM Notification Service: firebase-credentials.json not found.');
                return;
            }

            $factory = (new Factory)->withServiceAccount($credentialsPath);
            $this->messaging = $factory->createMessaging();
        } catch (\Exception $e) {
            Log::error('Error initializing Firebase FCM: ' . $e->getMessage());
        }
    }

    /**
     * Send Push Notification via FCM
     * 
     * @param string $deviceToken Driver FCM Token
     * @param string $title Notification Title
     * @param string $body Notification Body
     * @param array $data Additional Data (optional)
     * @return bool
     */
    public function sendToDevice(string $deviceToken, string $title, string $body, array $data = []): bool
    {
        if (!$this->messaging) {
            Log::warning('FCM is not initialized, cannot send notification.');
            return false;
        }

        if (empty($deviceToken)) {
            Log::warning('FCM token is empty.');
            return false;
        }

        try {
            $message = CloudMessage::withTarget('token', $deviceToken)
                ->withNotification(Notification::create($title, $body))
                ->withData($data);

            $this->messaging->send($message);
            Log::info("FCM notification sent successfully to {$deviceToken}");
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to send FCM notification: " . $e->getMessage());
            return false;
        }
    }
}
