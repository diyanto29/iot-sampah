<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WasteLog extends Model
{
    public $timestamps = false;
    protected $fillable = ['waste_bin_id', 'category', 'confidence', 'sorted_at'];

    protected function casts(): array
    {
        return [
            'confidence' => 'float',
            'sorted_at' => 'datetime',
        ];
    }

    public function bin(): BelongsTo
    {
        return $this->belongsTo(WasteBin::class, 'waste_bin_id');
    }
}

