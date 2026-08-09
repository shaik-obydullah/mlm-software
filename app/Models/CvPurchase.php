<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CvPurchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'tier_id', 'amount', 'price', 'payment_method', 'status',
        'transaction_id', 'pin_code', 'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'price' => 'decimal:2',
            'paid_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tier(): BelongsTo
    {
        return $this->belongsTo(CvTier::class, 'tier_id');
    }

    public function activation(): HasOne
    {
        return $this->hasOne(CvActivation::class, 'purchase_id');
    }

    public function commissions(): HasMany
    {
        return $this->hasMany(Commission::class);
    }
}
