<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Helpers\DateHelper;

class ItemResource extends JsonResource
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
            'type' => $this->type,
            'status' => $this->status,
            'energy_level' => $this->energy_level,
            'time_estimate' => $this->time_estimate,
            'notes' => $this->notes,
            'waiting_for_person' => $this->waiting_for_person,
            'project' => $this->whenLoaded('project', function() {
                return [
                    'id' => $this->project->id,
                    'title' => $this->project->title,
                ];
            }),
            'context' => $this->whenLoaded('context', function() {
                return [
                    'id' => $this->context->id,
                    'name' => $this->context->name,
                    'color' => $this->context->color,
                ];
            }),
            'due_date' => $this->due_date ? DateHelper::formatForApi($this->due_date) : null,
            'reminder_date' => $this->reminder_date ? DateHelper::formatForApi($this->reminder_date) : null,
            'waiting_since' => $this->waiting_since ? DateHelper::formatForApi($this->waiting_since) : null,
            'created_at' => DateHelper::formatForApi($this->created_at),
            'updated_at' => DateHelper::formatForApi($this->updated_at),
        ];
    }
}