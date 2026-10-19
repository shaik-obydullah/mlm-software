<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InterestRate extends Model
{
    use HasFactory;

    protected $fillable = ['rate', 'effective_date', 'status'];

    protected function casts(): array
    {
        return [
            'rate' => 'decimal:4',
            'effective_date' => 'date',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active')
            ->where('effective_date', '<=', now());
    }
}
