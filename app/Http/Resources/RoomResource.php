<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoomResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'building_id' => $this->building_id,
            'code' => $this->code,
            'name' => $this->name,
            'floor' => $this->floor,
            'floor_label' => $this->floor_label,
            'type' => $this->type,
            'type_label' => $this->type_label,
            'capacity' => $this->capacity,
            'status' => $this->status,
            'status_label' => $this->status_label,
            'live_status' => $this->live_status,
            'notes' => $this->notes,
            'building' => $this->whenLoaded('building', fn () => [
                'id' => $this->building->id,
                'code' => $this->building->code,
                'name' => $this->building->name,
            ]),
            'facilities' => $this->whenLoaded('facilities', fn () => $this->facilities->map(fn ($facility) => [
                'id' => $facility->id,
                'code' => $facility->code,
                'name' => $facility->name,
                'category' => $facility->category,
                'unit' => $facility->unit,
                'quantity' => (int) $facility->pivot->quantity,
                'condition' => $facility->pivot->condition,
                'note' => $facility->pivot->note,
            ])),
            'facility_conditions' => $this->facilityConditions(),
            'upcoming_sessions_count' => $this->whenCounted('defenseSessions'),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
