<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CvTier extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'amount', 'price', 'description', 'status'];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'price' => 'decimal:2',
        ];
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(CvPurchase::class, 'tier_id');
    }
}
