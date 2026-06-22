<?php

namespace App\Services;

use App\Models\ServiceReport;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppNotificationService
{
    /**
     * Send WhatsApp notification to service admin when new service report is submitted.
     */
    public function notifyServiceAdmin(ServiceReport $serviceReport): void
    {
        $gatewayUrl = config('services.wa.gateway_url');
        $token = config('services.wa.gateway_token');
        $adminPhone = config('services.wa.service_admin_phone');

        if (!$gatewayUrl || !$token || !$adminPhone) {
            Log::warning('WhatsApp gateway not configured properly');
            return;
        }

        $driverName = $serviceReport->driver->full_name ?? 'Unknown';
        $plateNumber = $serviceReport->vehicle->plate_number ?? 'N/A';
        $timestamp = now()->format('d-m-Y H:i');
        
        // Get customer from vehicle's project
        $customer = $serviceReport->getProjectCustomer() ?? $serviceReport->customer;
        $customerName = $customer->name ?? 'Belum di-link';
        
        $description = mb_substr($serviceReport->description, 0, 200);
        $url = url(route('admin.service.show', $serviceReport->id));

        $message = "🔧 LAPORAN SERVICE BARU\n";
        $message .= "─────────────────────────\n";
        $message .= "Driver  : {$driverName}\n";
        $message .= "Plat    : {$plateNumber}\n";
        $message .= "Tanggal : {$timestamp}\n";
        $message .= "Customer: {$customerName}\n";
        $message .= "Masalah : {$description}\n\n";
        $message .= "Mohon segera review:\n{$url}";

        try {
            Http::withHeaders([
                'Authorization' => $token,
            ])->timeout(10)->post($gatewayUrl, [
                'target' => $adminPhone,
                'message' => $message,
            ]);
        } catch (\Exception $e) {
            Log::error('WhatsApp notification failed: ' . $e->getMessage());
        }
    }

    /**
     * Send WhatsApp notification to customer when service report is approved by admin.
     */
    public function notifyCustomer(ServiceReport $serviceReport): void
    {
        $gatewayUrl = config('services.wa.gateway_url');
        $token = config('services.wa.gateway_token');
        
        // Get customer from vehicle's project (primary source)
        $customer = $serviceReport->getProjectCustomer() ?? $serviceReport->customer;
        
        if (!$customer) {
            Log::warning("Service report {$serviceReport->id} has no associated customer");
            return;
        }
        
        $customerPhone = $customer->phone ?? config('services.wa.customer_default_phone');

        if (!$gatewayUrl || !$token || !$customerPhone) {
            Log::warning('WhatsApp gateway not configured for customer notification');
            return;
        }

        $contactPerson = $customer->contact_person ?? 'Customer';
        $plateNumber = $serviceReport->vehicle->plate_number ?? 'N/A';
        $url = url(route('customer.approve.show', $serviceReport->id));

        $message = "📋 PERSETUJUAN SERVICE KENDARAAN\n";
        $message .= "─────────────────────────\n";
        $message .= "Yth. {$contactPerson},\n\n";
        $message .= "PT Hamada Logistik telah menyelesaikan\n";
        $message .= "service untuk unit {$plateNumber}.\n";
        $message .= "Mohon kesediaan Bapak/Ibu untuk\n";
        $message .= "meninjau dan menyetujui:\n\n";
        $message .= "{$url}\n\n";
        $message .= "Terima kasih.";

        try {
            Http::withHeaders([
                'Authorization' => $token,
            ])->timeout(10)->post($gatewayUrl, [
                'target' => $customerPhone,
                'message' => $message,
            ]);
        } catch (\Exception $e) {
            Log::error('WhatsApp customer notification failed: ' . $e->getMessage());
        }
    }

    /**
     * Send approval notification to driver for transport cost entry
     */
    public function sendApprovalNotification($tripEntry): void
    {
        $gatewayUrl = config('services.wa.gateway_url');
        $token = config('services.wa.gateway_token');
        
        $driver = $tripEntry->driver;
        $driverPhone = $driver->phone_number ?? null;

        if (!$gatewayUrl || !$token || !$driverPhone) {
            Log::warning('WhatsApp gateway not configured or driver has no phone number');
            return;
        }

        $message = "✅ *LAPORAN UANG JALAN DISETUJUI*\n\n";
        $message .= "Tanggal: " . $tripEntry->trip_date->format('d/m/Y') . "\n";
        $message .= "Total Biaya: Rp " . number_format($tripEntry->total_cost, 0, ',', '.') . "\n";
        $message .= "Lembur: Rp " . number_format($tripEntry->overtime_payment, 0, ',', '.') . "\n";
        $message .= "Bonus: Rp " . number_format($tripEntry->bonus_driver, 0, ',', '.') . "\n";
        $message .= "Grand Total: Rp " . number_format($tripEntry->total_cost + $tripEntry->overtime_payment + $tripEntry->bonus_driver, 0, ',', '.') . "\n\n";
        $message .= "Terima kasih atas laporan Anda!";

        try {
            Http::withHeaders([
                'Authorization' => $token,
            ])->timeout(10)->post($gatewayUrl, [
                'target' => $driverPhone,
                'message' => $message,
            ]);
        } catch (\Exception $e) {
            Log::error('WhatsApp approval notification failed: ' . $e->getMessage());
        }

        // --- FCM NOTIFICATION ---
        try {
            if (!empty($driver->fcm_token)) {
                $fcmService = app(\App\Services\FcmNotificationService::class);
                $title = "Uang Jalan Disetujui ✅";
                $body = "Laporan uang jalan tanggal " . $tripEntry->trip_date->format('d/m/Y') . " sebesar Rp " . number_format($tripEntry->total_cost + $tripEntry->overtime_payment + $tripEntry->bonus_driver, 0, ',', '.') . " telah disetujui.";
                $fcmService->sendToDevice($driver->fcm_token, $title, $body);
            }
        } catch (\Exception $e) {
            Log::error('FCM approval notification failed: ' . $e->getMessage());
        }
    }

    /**
     * Send rejection notification to driver for transport cost entry
     */
    public function sendRejectionNotification($tripEntry): void
    {
        $gatewayUrl = config('services.wa.gateway_url');
        $token = config('services.wa.gateway_token');
        
        $driver = $tripEntry->driver;
        $driverPhone = $driver->phone_number ?? null;

        if (!$gatewayUrl || !$token || !$driverPhone) {
            Log::warning('WhatsApp gateway not configured or driver has no phone number');
            return;
        }

        $message = "❌ *LAPORAN UANG JALAN DITOLAK*\n\n";
        $message .= "Tanggal: " . $tripEntry->trip_date->format('d/m/Y') . "\n";
        $message .= "Alasan: " . $tripEntry->rejection_reason . "\n\n";
        $message .= "Silakan hubungi admin untuk informasi lebih lanjut.";

        try {
            Http::withHeaders([
                'Authorization' => $token,
            ])->timeout(10)->post($gatewayUrl, [
                'target' => $driverPhone,
                'message' => $message,
            ]);
        } catch (\Exception $e) {
            Log::error('WhatsApp rejection notification failed: ' . $e->getMessage());
        }

        // --- FCM NOTIFICATION ---
        try {
            if (!empty($driver->fcm_token)) {
                $fcmService = app(\App\Services\FcmNotificationService::class);
                $title = "Uang Jalan Ditolak ❌";
                $body = "Laporan uang jalan tanggal " . $tripEntry->trip_date->format('d/m/Y') . " ditolak. Alasan: " . $tripEntry->rejection_reason;
                $fcmService->sendToDevice($driver->fcm_token, $title, $body);
            }
        } catch (\Exception $e) {
            Log::error('FCM rejection notification failed: ' . $e->getMessage());
        }
    }

    /**
     * Send WhatsApp notification to admin when customer requests revision.
     */
    public function notifyAdminOnRevision(ServiceReport $serviceReport): void
    {
        $gatewayUrl = config('services.wa.gateway_url');
        $token = config('services.wa.gateway_token');
        $adminPhone = config('services.wa.service_admin_phone');

        if (!$gatewayUrl || !$token || !$adminPhone) {
            Log::warning('WhatsApp gateway not configured properly for admin notification');
            return;
        }

        $plateNumber = $serviceReport->vehicle->plate_number ?? 'N/A';
        $customer = $serviceReport->getProjectCustomer() ?? $serviceReport->customer;
        $customerName = $customer->name ?? 'Customer';
        
        $notes = mb_substr($serviceReport->customer_revision_notes, 0, 200);
        $url = url(route('admin.service.show', $serviceReport->id));

        $message = "⚠️ REQUEST KOREKSI SERVICE\n";
        $message .= "─────────────────────────\n";
        $message .= "Customer: {$customerName}\n";
        $message .= "Plat    : {$plateNumber}\n";
        $message .= "Catatan : {$notes}\n\n";
        $message .= "Mohon segera diperbaiki:\n{$url}";

        try {
            Http::withHeaders([
                'Authorization' => $token,
            ])->timeout(10)->post($gatewayUrl, [
                'target' => $adminPhone,
                'message' => $message,
            ]);
        } catch (\Exception $e) {
            Log::error('WhatsApp admin notification (revision) failed: ' . $e->getMessage());
        }
    }

    /**
     * Send WhatsApp notification to admin when customer rejects report.
     */
    public function notifyAdminOnRejection(ServiceReport $serviceReport): void
    {
        $gatewayUrl = config('services.wa.gateway_url');
        $token = config('services.wa.gateway_token');
        $adminPhone = config('services.wa.service_admin_phone');

        if (!$gatewayUrl || !$token || !$adminPhone) {
            Log::warning('WhatsApp gateway not configured properly for admin notification');
            return;
        }

        $plateNumber = $serviceReport->vehicle->plate_number ?? 'N/A';
        $customer = $serviceReport->getProjectCustomer() ?? $serviceReport->customer;
        $customerName = $customer->name ?? 'Customer';
        
        $reason = mb_substr($serviceReport->customer_rejection_reason, 0, 200);
        $url = url(route('admin.service.show', $serviceReport->id));

        $message = "❌ LAPORAN SERVICE DITOLAK\n";
        $message .= "─────────────────────────\n";
        $message .= "Customer: {$customerName}\n";
        $message .= "Plat    : {$plateNumber}\n";
        $message .= "Alasan  : {$reason}\n\n";
        $message .= "Cek laporan:\n{$url}";

        try {
            Http::withHeaders([
                'Authorization' => $token,
            ])->timeout(10)->post($gatewayUrl, [
                'target' => $adminPhone,
                'message' => $message,
            ]);
        } catch (\Exception $e) {
            Log::error('WhatsApp admin notification (rejection) failed: ' . $e->getMessage());
        }
    }
}
