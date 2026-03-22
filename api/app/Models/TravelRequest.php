<?php

namespace App\Models;

use Database\Factories\TravelRequestFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TravelRequest extends Model
{
    /** @use HasFactory<TravelRequestFactory> */
    use HasFactory;

    protected $fillable = [
        'status',
        'destination',
        'start_date',
        'end_date',
        'user_id',
        'admin_id',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}
