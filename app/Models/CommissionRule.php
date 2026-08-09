<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommissionRule extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'type', 'rate', 'min_volume', 'level', 'status'];

    protected function casts(): array
    {
        return [
            'rate' => 'decimal:4',
            'min_volume' => 'integer',
            'level' => 'integer',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
