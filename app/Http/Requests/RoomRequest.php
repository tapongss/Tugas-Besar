<?php

namespace App\Http\Requests;

use App\Models\Facility;
use App\Models\Room;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class RoomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage-module') ?? false;
    }

    protected function prepareForValidation(): void
    {
        $facilities = collect($this->input('facilities', []))
            ->filter(fn ($item) => filled($item['facility_id'] ?? null))
            ->map(fn ($item) => [
                'facility_id' => (int) $item['facility_id'],
                'quantity' => (int) ($item['quantity'] ?? 1),
                'condition' => $item['condition'] ?? 'baik',
                'note' => $item['note'] ?? null,
            ])
            ->values()
            ->all();

        $this->merge([
            'code' => Str::upper(trim((string) $this->input('code'))),
            'facilities' => $facilities,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $room = $this->route('room');

        return [
            'building_id' => ['required', 'integer', Rule::exists('buildings', 'id')],
            'code' => ['required', 'string', 'max:30', 'alpha_dash', Rule::unique('rooms', 'code')->ignore($room)],
            'name' => ['required', 'string', 'max:120'],
            'floor' => ['required', 'integer', 'min:0', 'max:100'],
            'type' => ['required', Rule::in(array_keys(Room::types()))],
            'capacity' => ['required', 'integer', 'min:1', 'max:2000'],
            'status' => ['required', Rule::in(array_keys(Room::statuses()))],
            'notes' => ['nullable', 'string', 'max:1000'],
            'facilities' => ['array', 'max:30'],
            'facilities.*.facility_id' => ['required', 'integer', 'distinct', Rule::exists('facilities', 'id')],
            'facilities.*.quantity' => ['required', 'integer', 'min:1', 'max:999'],
            'facilities.*.condition' => ['required', Rule::in(array_keys(Facility::conditions()))],
            'facilities.*.note' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'code.unique' => 'Kode ruang sudah digunakan.',
            'facilities.*.facility_id.distinct' => 'Fasilitas yang sama tidak boleh ditambahkan dua kali.',
            'capacity.min' => 'Kapasitas ruang minimal 1 orang.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'building_id' => 'gedung',
            'code' => 'kode ruang',
            'name' => 'nama ruang',
            'floor' => 'lantai',
            'type' => 'jenis ruang',
            'capacity' => 'kapasitas',
            'status' => 'status ruang',
            'notes' => 'catatan',
            'facilities.*.facility_id' => 'fasilitas',
            'facilities.*.quantity' => 'jumlah fasilitas',
            'facilities.*.condition' => 'kondisi fasilitas',
        ];
    }
}
