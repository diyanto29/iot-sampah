<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CapacityAlert extends Model
{
    protected $fillable = [
        'waste_bin_id',
        'compartment',
        'capacity_percent',
        'status',
        'handled_by',
        'handled_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'capacity_percent' => 'float',
            'handled_at' => 'datetime',
        ];
    }

    public function bin(): BelongsTo
    {
        return $this->belongsTo(WasteBin::class, 'waste_bin_id');
    }

    public function handler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by');
    }
}

