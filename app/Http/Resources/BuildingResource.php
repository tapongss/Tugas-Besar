<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BuildingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'address' => $this->address,
            'floors' => $this->floors,
            'notes' => $this->notes,
            'is_active' => $this->is_active,
            'status_label' => $this->status_label,
            'rooms_count' => $this->whenCounted('rooms'),
            'capacity_total' => $this->when(isset($this->capacity_total), fn () => (int) $this->capacity_total),
            'rooms' => RoomResource::collection($this->whenLoaded('rooms')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
