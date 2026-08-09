<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommissionBatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'period_start', 'period_end', 'total_amount', 'status', 'summary', 'processed_by',
    ];

    protected function casts(): array
    {
        return [
            'period_start' => 'datetime',
            'period_end' => 'datetime',
            'total_amount' => 'decimal:2',
            'summary' => 'array',
        ];
    }

    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }
}
