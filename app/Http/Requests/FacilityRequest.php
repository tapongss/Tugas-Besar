<?php

namespace App\Http\Requests;

use App\Models\Facility;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class FacilityRequest extends FormRequest
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
        $facility = $this->route('facility');

        return [
            'code' => ['required', 'string', 'max:20', 'alpha_dash', Rule::unique('facilities', 'code')->ignore($facility)],
            'name' => ['required', 'string', 'max:120'],
            'category' => ['required', Rule::in(array_keys(Facility::categories()))],
            'unit' => ['required', 'string', 'max:30'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'code.unique' => 'Kode fasilitas sudah digunakan.',
            'category.in' => 'Kategori fasilitas tidak valid.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'code' => 'kode fasilitas',
            'name' => 'nama fasilitas',
            'category' => 'kategori',
            'unit' => 'satuan',
            'description' => 'deskripsi',
            'is_active' => 'status aktif',
        ];
    }
}
