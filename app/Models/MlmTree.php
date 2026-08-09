<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MlmTree extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'parent_id', 'position', 'level', 'path'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function leftChild(): HasOne
    {
        return $this->hasOne(self::class, 'parent_id')->where('position', 'left');
    }

    public function rightChild(): HasOne
    {
        return $this->hasOne(self::class, 'parent_id')->where('position', 'right');
    }
}
