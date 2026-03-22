<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HealthResource extends JsonResource
{
    public static $wrap = 'data';

    public function toArray(Request $request): array
    {
        return [
            'laravel_version' => $this->resource['laravel_version'],
            'mysql_version' => $this->resource['mysql_version'],
            'database_connection_status' => $this->resource['database_connection_status'],
        ];
    }
}
