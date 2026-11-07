<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ScheduleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'room_id' => $this->room_id,
            'title' => $this->title,
            'student_name' => $this->student_name,
            'student_nim' => $this->student_nim,
            'type' => $this->type,
            'type_label' => $this->type_label,
            'date' => $this->date?->format('Y-m-d'),
            'start_time' => $this->start_time_label,
            'end_time' => $this->end_time_label,
            'time_range' => $this->time_range,
            'duration_minutes' => $this->duration,
            'status' => $this->status,
            'status_label' => $this->status_label,
            'supervisor_name' => $this->supervisor_name,
            'examiner_name' => $this->examiner_name,
            'notes' => $this->notes,
            'room' => $this->whenLoaded('room', fn () => [
                'id' => $this->room->id,
                'code' => $this->room->code,
                'name' => $this->room->name,
                'building' => $this->room->building?->name,
            ]),
            'creator' => $this->whenLoaded('creator', fn () => $this->creator ? [
                'id' => $this->creator->id,
                'name' => $this->creator->name,
            ] : null),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
