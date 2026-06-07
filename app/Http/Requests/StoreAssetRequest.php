<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreAssetRequest extends FormRequest
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
        return [
            'plate_number' => 'required|unique:vehicles,plate_number|max:15',
            'type' => 'required|string|max:50',
            'tahun_pembuatan' => 'nullable|integer|min:1900|max:' . date('Y'),
            'current_km' => 'required|numeric|min:0',
            'project_id' => 'nullable|exists:projects,id',
            'pajak_stnk_berlaku_sampai' => 'nullable|date',
            'kir_berlaku_sampai' => 'nullable|date',
            'status' => 'nullable|string|max:50',
            'is_temporary' => 'nullable|boolean',
            'verification_status' => 'nullable|string|max:30',
            'source' => 'nullable|string|max:30',
            'notes' => 'nullable|string',
            'service_interval_km' => 'nullable|integer|min:0',
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
