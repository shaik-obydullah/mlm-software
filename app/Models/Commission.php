<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Commission extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'from_user_id', 'cv_purchase_id', 'type', 'amount',
        'cv_volume', 'level', 'status', 'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'cv_volume' => 'decimal:2',
            'level' => 'integer',
            'paid_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function fromUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(CvPurchase::class, 'cv_purchase_id');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}
