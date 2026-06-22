<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Driver;
use App\Models\ServiceReport;
use App\Models\TransportCost;
use Carbon\Carbon;

class DriverGuidanceService
{
    public function buildFor(Driver $driver): array
    {
        $now = now();
        $activeAttendance = Attendance::with('vehicle')
            ->where('driver_id', $driver->id)
            ->whereNull('time_out')
            ->latest('time_in')
            ->first();

        $latestServiceReport = ServiceReport::with('vehicle')
            ->where('driver_id', $driver->id)
            ->whereIn('status', [
                ServiceReport::STATUS_WAITING_COMPLETION,
                ServiceReport::STATUS_PENDING,
                ServiceReport::STATUS_PENDING_ADMIN,
                ServiceReport::STATUS_PENDING_CUSTOMER,
                ServiceReport::STATUS_REVISION_REQUESTED,
                ServiceReport::STATUS_REJECTED_CUSTOMER,
            ])
            ->latest('timestamp')
            ->first();

        $transportCostPrompt = $this->transportCostPrompt($driver, $activeAttendance, $now);
        $alerts = array_values(array_filter([
            $this->simAlert($driver, $now),
            $this->clockOutAlert($activeAttendance, $now),
            $this->manualVehicleAlert($activeAttendance),
            $this->vehicleStatusAlert($activeAttendance),
            $this->serviceReportAlert($latestServiceReport),
            $transportCostPrompt,
        ]));

        return [
            'server_time' => $now->toDateTimeString(),
            'driver' => [
                'id' => $driver->driver_id_nik,
                'name' => $driver->full_name,
                'project_id' => $driver->project_id,
            ],
            'duty' => $this->dutyPayload($activeAttendance, $now),
            'primary_instruction' => $this->primaryInstruction($activeAttendance, $transportCostPrompt, $now),
            'alerts' => $alerts,
            'next_steps' => $this->nextSteps($activeAttendance),
            'quick_actions' => $this->quickActions($activeAttendance, $latestServiceReport),
            'low_device_mode' => $this->lowDeviceMode(),
            'voice' => [
                'enabled' => true,
                'language' => 'id-ID',
                'use_preloaded_audio' => true,
                'fallback_to_text' => true,
            ],
        ];
    }

    private function dutyPayload(?Attendance $attendance, Carbon $now): array
    {
        if (!$attendance) {
            return [
                'is_on_duty' => false,
                'attendance_id' => null,
                'plate_number' => null,
                'time_in' => null,
                'duration_minutes' => 0,
            ];
        }

        $timeIn = Carbon::parse($attendance->time_in);

        return [
            'is_on_duty' => true,
            'attendance_id' => $attendance->id,
            'plate_number' => $attendance->vehicle->plate_number ?? $attendance->manual_vehicle_plate,
            'vehicle_id' => $attendance->vehicle_id,
            'vehicle_status' => $attendance->vehicle->status ?? null,
            'vehicle_entry_method' => $attendance->vehicle_entry_method ?? 'qr',
            'vehicle_verification_status' => $attendance->vehicle_verification_status ?? 'verified',
            'time_in' => $timeIn->toDateTimeString(),
            'duration_minutes' => $timeIn->diffInMinutes($now),
        ];
    }

    private function primaryInstruction(?Attendance $attendance, ?array $transportCostPrompt, Carbon $now): array
    {
        if (!$attendance) {
            if ($transportCostPrompt) {
                return [
                    'type' => 'transport_cost',
                    'severity' => 'info',
                    'title' => 'Ajukan uang jalan',
                    'message' => 'Tugas hari ini sudah selesai. Silakan isi laporan uang jalan sebelum lupa.',
                    'action' => 'open_transport_cost_form',
                    'voice_text' => 'Tugas hari ini sudah selesai. Silakan isi laporan uang jalan.',
                    'audio_key' => 'transport_cost_ready',
                ];
            }

            return [
                'type' => 'check_in',
                'severity' => 'info',
                'title' => 'Mulai tugas',
                'message' => 'Silakan mulai dari scan QR driver, lalu scan QR unit kendaraan.',
                'action' => 'start_check_in',
                'voice_text' => 'Silakan mulai tugas. Scan QR driver terlebih dahulu.',
                'audio_key' => 'start_check_in',
            ];
        }

        $durationMinutes = Carbon::parse($attendance->time_in)->diffInMinutes($now);
        if ($durationMinutes >= 480) {
            return [
                'type' => 'clock_out_reminder',
                'severity' => 'warning',
                'title' => 'Jangan lupa akhiri tugas',
                'message' => 'Anda sudah bertugas lebih dari 8 jam. Jika pekerjaan selesai, segera lakukan clock-out.',
                'action' => 'open_clock_out',
                'voice_text' => 'Anda sudah bertugas lebih dari delapan jam. Jika sudah selesai, silakan akhiri tugas.',
                'audio_key' => 'clock_out_reminder',
            ];
        }

        return [
            'type' => 'on_duty',
            'severity' => 'success',
            'title' => 'Sedang bertugas',
            'message' => 'Anda sedang bertugas. Simpan tombol darurat dan akhiri tugas saat pekerjaan selesai.',
            'action' => 'show_duty_status',
            'voice_text' => 'Anda sedang bertugas. Jika sudah selesai, jangan lupa akhiri tugas.',
            'audio_key' => 'on_duty',
        ];
    }

    private function nextSteps(?Attendance $attendance): array
    {
        if (!$attendance) {
            return [
                ['key' => 'scan_driver_qr', 'label' => 'Scan QR Driver', 'completed' => false],
                ['key' => 'scan_or_input_vehicle', 'label' => 'Scan atau input unit', 'completed' => false],
                ['key' => 'photo_selfie', 'label' => 'Foto selfie', 'completed' => false],
                ['key' => 'photo_odometer_start', 'label' => 'Foto speedometer awal', 'completed' => false],
                ['key' => 'submit_check_in', 'label' => 'Kirim absen masuk', 'completed' => false],
            ];
        }

        return [
            ['key' => 'stay_on_duty', 'label' => 'Tetap bertugas', 'completed' => true],
            ['key' => 'report_emergency', 'label' => 'Lapor darurat bila ada kendala', 'completed' => false],
            ['key' => 'photo_odometer_end', 'label' => 'Foto speedometer akhir', 'completed' => false],
            ['key' => 'vehicle_checklist', 'label' => 'Checklist ban, lampu, rem', 'completed' => false],
            ['key' => 'submit_clock_out', 'label' => 'Akhiri tugas', 'completed' => false],
        ];
    }

    private function quickActions(?Attendance $attendance, ?ServiceReport $serviceReport): array
    {
        $actions = [
            [
                'key' => 'help',
                'label' => 'Saya Bingung',
                'action' => 'open_context_help',
                'style' => 'secondary',
                'icon' => 'help-circle',
            ],
            [
                'key' => 'emergency',
                'label' => 'Lapor Darurat',
                'action' => 'open_emergency_report',
                'style' => 'danger',
                'icon' => 'alert-triangle',
            ],
        ];

        if ($attendance) {
            array_unshift($actions, [
                'key' => 'clock_out',
                'label' => 'Akhiri Tugas',
                'action' => 'open_clock_out',
                'style' => 'primary',
                'icon' => 'log-out',
            ]);
        } else {
            array_unshift($actions, [
                'key' => 'check_in',
                'label' => 'Mulai Tugas',
                'action' => 'start_check_in',
                'style' => 'primary',
                'icon' => 'scan-line',
            ]);
        }

        if ($serviceReport?->status === ServiceReport::STATUS_WAITING_COMPLETION) {
            $actions[] = [
                'key' => 'complete_service',
                'label' => 'Service Selesai',
                'action' => 'open_service_completion',
                'style' => 'warning',
                'icon' => 'wrench',
                'resource_id' => $serviceReport->id,
            ];
        }

        return $actions;
    }

    private function simAlert(Driver $driver, Carbon $now): ?array
    {
        if (!$driver->sim_expiry_date) {
            return [
                'type' => 'sim_missing',
                'severity' => 'warning',
                'title' => 'Data SIM belum lengkap',
                'message' => 'Mohon hubungi admin untuk melengkapi data SIM agar tidak mengganggu operasional.',
                'action' => 'contact_admin',
                'voice_text' => 'Data SIM belum lengkap. Silakan hubungi admin.',
                'audio_key' => 'sim_missing',
            ];
        }

        $daysLeft = $now->copy()->startOfDay()->diffInDays($driver->sim_expiry_date->copy()->startOfDay(), false);

        if ($daysLeft < 0) {
            return [
                'type' => 'sim_expired',
                'severity' => 'danger',
                'title' => 'SIM sudah habis masa berlaku',
                'message' => 'SIM Anda sudah habis masa berlaku. Segera hubungi admin sebelum bertugas.',
                'action' => 'contact_admin',
                'voice_text' => 'SIM Anda sudah habis masa berlaku. Segera hubungi admin.',
                'audio_key' => 'sim_expired',
            ];
        }

        if ($daysLeft <= 30) {
            return [
                'type' => 'sim_expiring',
                'severity' => 'warning',
                'title' => 'SIM hampir habis',
                'message' => "Masa berlaku SIM habis dalam {$daysLeft} hari. Mohon siapkan perpanjangan.",
                'action' => 'contact_admin',
                'voice_text' => "Masa berlaku SIM habis dalam {$daysLeft} hari.",
                'audio_key' => 'sim_expiring',
            ];
        }

        return null;
    }

    private function clockOutAlert(?Attendance $attendance, Carbon $now): ?array
    {
        if (!$attendance) {
            return null;
        }

        $durationMinutes = Carbon::parse($attendance->time_in)->diffInMinutes($now);

        if ($durationMinutes < 480) {
            return null;
        }

        return [
            'type' => 'clock_out_reminder',
            'severity' => $durationMinutes >= 720 ? 'danger' : 'warning',
            'title' => 'Durasi tugas panjang',
            'message' => $durationMinutes >= 720
                ? 'Anda sudah bertugas lebih dari 12 jam. Jika pekerjaan selesai, segera clock-out atau hubungi admin.'
                : 'Anda sudah bertugas lebih dari 8 jam. Jangan lupa istirahat dan clock-out saat selesai.',
            'action' => 'open_clock_out',
            'voice_text' => $durationMinutes >= 720
                ? 'Anda sudah bertugas lebih dari dua belas jam. Segera akhiri tugas jika sudah selesai.'
                : 'Anda sudah bertugas lebih dari delapan jam. Jangan lupa akhiri tugas saat selesai.',
            'audio_key' => $durationMinutes >= 720 ? 'clock_out_urgent' : 'clock_out_reminder',
        ];
    }

    private function manualVehicleAlert(?Attendance $attendance): ?array
    {
        if (!$attendance || ($attendance->vehicle_entry_method ?? 'qr') !== 'manual') {
            return null;
        }

        if (($attendance->vehicle_verification_status ?? 'verified') !== 'pending') {
            return null;
        }

        return [
            'type' => 'manual_vehicle_pending',
            'severity' => 'info',
            'title' => 'Unit pengganti menunggu verifikasi',
            'message' => 'Unit ini sudah tercatat dari input manual dan sedang menunggu verifikasi admin.',
            'action' => 'show_vehicle_status',
            'voice_text' => 'Unit pengganti sedang menunggu verifikasi admin.',
            'audio_key' => 'vehicle_pending_verification',
        ];
    }

    private function vehicleStatusAlert(?Attendance $attendance): ?array
    {
        $vehicle = $attendance?->vehicle;
        if (!$vehicle) {
            return null;
        }

        $status = strtolower(trim((string) $vehicle->status));
        if (!in_array($status, ['rusak', 'servis', 'service', 'maintenance', 'perbaikan'], true)) {
            return null;
        }

        return [
            'type' => 'vehicle_status_problem',
            'severity' => 'danger',
            'title' => 'Status unit bermasalah',
            'message' => 'Unit ini tercatat sedang rusak/service. Hubungi admin sebelum melanjutkan.',
            'action' => 'contact_admin',
            'voice_text' => 'Unit ini tercatat sedang rusak atau service. Hubungi admin.',
            'audio_key' => 'vehicle_problem',
        ];
    }

    private function serviceReportAlert(?ServiceReport $report): ?array
    {
        if (!$report) {
            return null;
        }

        return match ($report->status) {
            ServiceReport::STATUS_WAITING_COMPLETION => [
                'type' => 'service_waiting_completion',
                'severity' => 'warning',
                'title' => 'Lengkapi service selesai',
                'message' => 'Ada laporan kendaraan rusak yang masih menunggu data service selesai.',
                'action' => 'open_service_completion',
                'resource_id' => $report->id,
                'voice_text' => 'Ada laporan kendaraan rusak. Lengkapi service selesai setelah perbaikan selesai.',
                'audio_key' => 'service_waiting_completion',
            ],
            ServiceReport::STATUS_PENDING, ServiceReport::STATUS_PENDING_ADMIN => [
                'type' => 'service_pending_admin',
                'severity' => 'info',
                'title' => 'Laporan service diterima',
                'message' => 'Laporan service sudah terkirim dan menunggu review admin.',
                'action' => 'show_service_report',
                'resource_id' => $report->id,
                'voice_text' => 'Laporan service sudah terkirim dan menunggu review admin.',
                'audio_key' => 'service_pending_admin',
            ],
            ServiceReport::STATUS_PENDING_CUSTOMER => [
                'type' => 'service_pending_customer',
                'severity' => 'info',
                'title' => 'Menunggu customer',
                'message' => 'Laporan service sudah dikirim ke customer untuk konfirmasi.',
                'action' => 'show_service_report',
                'resource_id' => $report->id,
                'voice_text' => 'Laporan service sudah dikirim ke customer.',
                'audio_key' => 'service_pending_customer',
            ],
            ServiceReport::STATUS_REVISION_REQUESTED => [
                'type' => 'service_revision_requested',
                'severity' => 'warning',
                'title' => 'Customer minta klarifikasi',
                'message' => 'Customer meminta klarifikasi pada laporan service. Admin akan menindaklanjuti.',
                'action' => 'show_service_report',
                'resource_id' => $report->id,
                'voice_text' => 'Customer meminta klarifikasi laporan service.',
                'audio_key' => 'service_revision_requested',
            ],
            ServiceReport::STATUS_REJECTED_CUSTOMER => [
                'type' => 'service_rejected_customer',
                'severity' => 'danger',
                'title' => 'Laporan ditolak customer',
                'message' => 'Laporan service ditolak customer. Hubungi admin untuk tindak lanjut.',
                'action' => 'contact_admin',
                'resource_id' => $report->id,
                'voice_text' => 'Laporan service ditolak customer. Hubungi admin.',
                'audio_key' => 'service_rejected_customer',
            ],
            default => null,
        };
    }

    private function transportCostPrompt(Driver $driver, ?Attendance $activeAttendance, Carbon $now): ?array
    {
        if ($activeAttendance) {
            return null;
        }

        $attendance = Attendance::where('driver_id', $driver->id)
            ->whereDate('time_in', $now->toDateString())
            ->whereNotNull('time_out')
            ->latest('time_out')
            ->first();

        if (!$attendance) {
            return null;
        }

        $alreadySubmitted = TransportCost::where('driver_id', $driver->id)
            ->whereDate('trip_date', $now->toDateString())
            ->exists();

        if ($alreadySubmitted) {
            return null;
        }

        return [
            'type' => 'transport_cost_ready',
            'severity' => 'info',
            'title' => 'Uang jalan belum diisi',
            'message' => 'Anda sudah clock-out hari ini. Silakan isi laporan uang jalan jika ada biaya perjalanan.',
            'action' => 'open_transport_cost_form',
            'attendance_id' => $attendance->id,
            'voice_text' => 'Anda sudah clock-out hari ini. Silakan isi uang jalan jika ada biaya perjalanan.',
            'audio_key' => 'transport_cost_ready',
        ];
    }

    private function lowDeviceMode(): array
    {
        return [
            'recommended' => true,
            'ui' => [
                'large_buttons' => true,
                'high_contrast' => true,
                'single_step_forms' => true,
                'disable_heavy_animations' => true,
                'show_loading_text' => true,
            ],
            'camera' => [
                'max_width' => 1280,
                'jpeg_quality' => 70,
                'prefer_camera_over_gallery' => true,
                'retry_if_blur_or_dark' => true,
            ],
            'network' => [
                'save_draft_first' => true,
                'auto_retry' => true,
                'offline_clock_out_supported' => true,
            ],
        ];
    }
}
