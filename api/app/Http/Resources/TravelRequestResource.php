<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TravelRequestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'destination' => $this->destination,
            'start_date' => $this->start_date->toDateString(),
            'end_date' => $this->end_date->toDateString(),
            'user' => new UserResource($this->user),
            'admin' => new UserResource($this->whenLoaded('admin')),
            'created_at' => $this->created_at,
        ];
    }
}
