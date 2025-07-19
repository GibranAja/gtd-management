<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Helpers\DateHelper;

class ProjectResource extends JsonResource
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
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status,
            'progress_percentage' => $this->progress_percentage,
            'items_count' => $this->whenCounted('items'),
            'active_items_count' => $this->whenCounted('activeItems'),
            'next_actions_count' => $this->whenCounted('nextActions'),
            'due_date' => $this->due_date ? DateHelper::formatForApi($this->due_date) : null,
            'created_at' => DateHelper::formatForApi($this->created_at),
            'updated_at' => DateHelper::formatForApi($this->updated_at),
        ];
    }
}