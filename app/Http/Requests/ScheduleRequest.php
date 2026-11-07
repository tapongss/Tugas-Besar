<?php

namespace App\Http\Requests;

use App\Models\DefenseSession;
use App\Models\Room;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class ScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage-module') ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'student_nim' => trim((string) $this->input('student_nim')),
            'start_time' => DefenseSession::normalizeTime($this->input('start_time')),
            'end_time' => DefenseSession::normalizeTime($this->input('end_time')),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $session = $this->route('schedule');

        return [
            'room_id' => ['required', 'integer', Rule::exists('rooms', 'id')],
            'title' => ['required', 'string', 'max:160'],
            'student_name' => ['required', 'string', 'max:120'],
            'student_nim' => ['required', 'string', 'max:20', 'regex:/^[0-9]+$/'],
            'type' => ['required', Rule::in(array_keys(DefenseSession::types()))],
            'date' => array_filter([
                'required',
                'date',
                $session ? null : 'after_or_equal:today',
            ]),
            'start_time' => ['required', 'date_format:H:i:s'],
            'end_time' => ['required', 'date_format:H:i:s', 'after:start_time'],
            'status' => ['required', Rule::in(array_keys(DefenseSession::statuses()))],
            'supervisor_name' => ['nullable', 'string', 'max:120'],
            'examiner_name' => ['nullable', 'string', 'max:120'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $room = Room::find($this->input('room_id'));
            $session = $this->route('schedule');

            if ($room && $room->status !== 'tersedia' && $this->input('status') !== DefenseSession::STATUS_CANCELLED) {
                $validator->errors()->add('room_id', "Ruang {$room->code} sedang {$room->status_label} dan tidak dapat dijadwalkan.");
            }

            $conflict = DefenseSession::query()
                ->with('room')
                ->conflicting(
                    (int) $this->input('room_id'),
                    (string) $this->input('date'),
                    (string) $this->input('start_time'),
                    (string) $this->input('end_time'),
                    $session?->id
                )
                ->first();

            if ($conflict) {
                $validator->errors()->add(
                    'start_time',
                    "Jadwal bentrok dengan \"{$conflict->title}\" ({$conflict->student_name}) pada {$conflict->time_range} di ruang yang sama."
                );
            }

            if ($this->input('start_time') >= $this->input('end_time')) {
                $validator->errors()->add('end_time', 'Waktu selesai harus setelah waktu mulai.');
            }
        });
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'student_nim.regex' => 'NIM hanya boleh berisi angka.',
            'end_time.after' => 'Waktu selesai harus setelah waktu mulai.',
            'date.date' => 'Tanggal sidang tidak valid.',
            'start_time.date_format' => 'Format waktu mulai tidak valid.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'room_id' => 'ruang sidang',
            'title' => 'judul kegiatan',
            'student_name' => 'nama mahasiswa',
            'student_nim' => 'NIM',
            'type' => 'jenis sidang',
            'date' => 'tanggal',
            'start_time' => 'waktu mulai',
            'end_time' => 'waktu selesai',
            'status' => 'status',
            'supervisor_name' => 'dosen pembimbing',
            'examiner_name' => 'dosen penguji',
            'notes' => 'catatan',
        ];
    }
}
