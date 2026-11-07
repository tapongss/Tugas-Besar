<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class BuildingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage-module') ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'code' => Str::upper(trim((string) $this->input('code'))),
            'is_active' => $this->boolean('is_active'),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $building = $this->route('building');

        return [
            'code' => ['required', 'string', 'max:20', 'alpha_dash', Rule::unique('buildings', 'code')->ignore($building)],
            'name' => ['required', 'string', 'max:120'],
            'address' => ['nullable', 'string', 'max:255'],
            'floors' => ['required', 'integer', 'min:1', 'max:50'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'code.unique' => 'Kode gedung sudah digunakan.',
            'code.alpha_dash' => 'Kode gedung hanya boleh berisi huruf, angka, tanda hubung, dan garis bawah.',
            'floors.min' => 'Jumlah lantai minimal 1.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'code' => 'kode gedung',
            'name' => 'nama gedung',
            'address' => 'alamat',
            'floors' => 'jumlah lantai',
            'notes' => 'catatan',
            'is_active' => 'status aktif',
        ];
    }
}
