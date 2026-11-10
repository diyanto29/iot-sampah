<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WasteBin extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['id', 'location_name', 'bin_height_cm', 'is_active', 'last_active_at'];

    protected function casts(): array
    {
        return [
            'bin_height_cm' => 'float',
            'is_active' => 'boolean',
            'last_active_at' => 'datetime',
        ];
    }

    public function logs(): HasMany
    {
        return $this->hasMany(WasteLog::class, 'waste_bin_id');
    }

    public function capacities(): HasMany
    {
        return $this->hasMany(BinCapacity::class, 'waste_bin_id');
    }

    public function alerts(): HasMany
    {
        return $this->hasMany(CapacityAlert::class, 'waste_bin_id');
    }
}

