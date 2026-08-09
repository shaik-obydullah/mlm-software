<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CvActivation extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_id', 'user_id', 'activation_date', 'expiry_date', 'status',
    ];

    protected function casts(): array
    {
        return [
            'activation_date' => 'datetime',
            'expiry_date' => 'datetime',
        ];
    }

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(CvPurchase::class, 'purchase_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
