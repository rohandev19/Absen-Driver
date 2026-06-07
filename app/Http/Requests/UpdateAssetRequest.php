<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateAssetRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $vehicleId = $this->route('vehicle');
        if (is_object($vehicleId)) {
            $vehicleId = $vehicleId->id;
        }

        return [
            'plate_number' => 'required|string|max:15|unique:vehicles,plate_number,' . $vehicleId,
            'type' => 'required|string|max:50',
            'tahun_pembuatan' => 'nullable|integer|min:1900|max:' . date('Y'),
            'project_id' => 'nullable|exists:projects,id',
            'status' => 'nullable|string|max:50',
            'is_temporary' => 'nullable|boolean',
            'verification_status' => 'nullable|string|max:30',
            'source' => 'nullable|string|max:30',
            'notes' => 'nullable|string',
            'current_km' => 'nullable|numeric|min:0',
            'service_interval_km' => 'nullable|integer|min:0',
            'last_service_km' => 'nullable|integer|min:0',
            'pajak_stnk_berlaku_sampai' => 'nullable|date',
            'kir_berlaku_sampai' => 'nullable|date',
        ];
    }

    protected function prepareForValidation()
    {
        if ($this->has('plate_number')) {
            $this->merge([
                'plate_number' => strtoupper(str_replace(' ', '', $this->plate_number))
            ]);
        }
    }
}
