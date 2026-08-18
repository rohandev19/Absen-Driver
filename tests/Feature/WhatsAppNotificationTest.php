<?php

namespace Tests\Feature;

use App\Services\WhatsAppNotificationService;
use Tests\TestCase;

class WhatsAppNotificationTest extends TestCase
{
    /**
     * Ensure the WhatsApp Notification Service can be instantiated
     * and has the necessary methods.
     */
    public function test_whatsapp_service_exists()
    {
        $service = app(WhatsAppNotificationService::class);
        $this->assertInstanceOf(WhatsAppNotificationService::class, $service);
        $this->assertTrue(method_exists($service, 'notifyServiceAdmin'));
    }
}
