<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FacilityResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'category' => $this->category,
            'category_label' => $this->category_label,
            'unit' => $this->unit,
            'description' => $this->description,
            'is_active' => $this->is_active,
            'rooms_count' => $this->whenCounted('rooms'),
            'total_units' => $this->when(isset($this->total_units), fn () => (int) $this->total_units),
            'rooms' => $this->whenLoaded('rooms', fn () => $this->rooms->map(fn ($room) => [
                'id' => $room->id,
                'code' => $room->code,
                'name' => $room->name,
                'building' => $room->building?->name,
                'quantity' => (int) $room->pivot->quantity,
                'condition' => $room->pivot->condition,
                'note' => $room->pivot->note,
            ])),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
