<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Helpers\DateHelper;

class ContextResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'icon' => $this->icon,
            'color' => $this->color,
            'active_items_count' => $this->whenCounted('activeItems'),
            'created_at' => DateHelper::formatForApi($this->created_at),
            'updated_at' => DateHelper::formatForApi($this->updated_at),
        ];
    }
}