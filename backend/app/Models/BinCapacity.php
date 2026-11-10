<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BinCapacity extends Model
{
    public $timestamps = false;
    protected $fillable = ['waste_bin_id', 'organik_percent', 'anorganik_percent', 'residu_percent', 'recorded_at'];

    protected function casts(): array
    {
        return [
            'organik_percent' => 'float',
            'anorganik_percent' => 'float',
            'residu_percent' => 'float',
            'recorded_at' => 'datetime',
        ];
    }

    public function bin(): BelongsTo
    {
        return $this->belongsTo(WasteBin::class, 'waste_bin_id');
    }
}

