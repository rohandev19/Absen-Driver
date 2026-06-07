<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    private string $gatewayUrl;
    private string $token;

    public function __construct()
    {
        $this->gatewayUrl = config('services.wa.gateway_url', 'https://api.fonnte.com/send');
        $this->token      = config('services.wa.gateway_token', '');
    }

    /**
     * Kirim pesan WhatsApp ke nomor tujuan.
     *
     * @param  string  $phone   Nomor tujuan (format: 08xxx atau 628xxx)
     * @param  string  $message Isi pesan
     * @return bool
     */
    public function send(string $phone, string $message): bool
    {
        if (empty($this->token)) {
            Log::warning('WhatsApp: WA_GATEWAY_TOKEN belum dikonfigurasi.');
            return false;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => $this->token,
            ])->post($this->gatewayUrl, [
                'target'  => $phone,
                'message' => $message,
                'countryCode' => '62',
            ]);

            $body = $response->json();

            if ($response->successful() && ($body['status'] ?? false) === true) {
                Log::info("WhatsApp terkirim ke {$phone}");
                return true;
            }

            Log::warning("WhatsApp gagal ke {$phone}: " . json_encode($body));
            return false;

        } catch (\Exception $e) {
            Log::error("WhatsApp exception: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Kirim notif ke admin (nomor dari WA_SERVICE_ADMIN_PHONE).
     */
    public function notifyAdmin(string $message): bool
    {
        $adminPhone = config('services.wa.service_admin_phone');
        if (empty($adminPhone)) {
            Log::warning('WhatsApp: WA_SERVICE_ADMIN_PHONE belum dikonfigurasi.');
            return false;
        }
        return $this->send($adminPhone, $message);
    }
}
