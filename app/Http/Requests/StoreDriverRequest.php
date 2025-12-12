<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules;
use App\Models\Driver;

/**
 * Class StoreDriverRequest
 * * Bertugas sebagai "Satpam" validasi.
 * * Mencegah data driver yang tidak lengkap/invalid masuk ke Controller.
 */
class StoreDriverRequest extends FormRequest
{
    /**
     * Tentukan apakah user boleh melakukan request ini.
     * Return true karena kita sudah pakai middleware 'is-master-admin' di route/controller.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Aturan validasi (Rules).
     * Di sini kita mendefinisikan syarat data yang valid.
     */
    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:255'],

            // Validasi NIK harus unik di tabel drivers
            'driver_id_nik' => ['required', 'string', 'max:255', 'unique:' . Driver::class],

            'sim_expiry_date' => ['required', 'date'],

            // Password wajib, harus dikonfirmasi (ketik ulang), dan memenuhi standar keamanan default Laravel
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ];
    }

    /**
     * Pesan error kustom (Opsional).
     * Biar pesan error-nya lebih enak dibaca manusia (Bahasa Indonesia).
     */
    public function messages(): array
    {
        return [
            'driver_id_nik.unique' => 'NIK Driver ini sudah terdaftar. Mohon cek kembali.',
            'sim_expiry_date.required' => 'Tanggal masa berlaku SIM wajib diisi.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ];
    }
}