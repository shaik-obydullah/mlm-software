<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

#[Fillable([
    'unique_id', 'sponsor_id', 'name', 'email', 'password', 'phone', 'role', 'status',
    'kyc_status', 'nominee_name', 'nominee_relation', 'nominee_phone',
    'bank_name', 'bank_account_name', 'bank_account_number', 'bank_ifsc',
    'balance', 'activated_at',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, HasRoles, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'activated_at' => 'datetime',
            'balance' => 'decimal:2',
        ];
    }

    public function sponsor(): BelongsTo
    {
        return $this->belongsTo(self::class, 'sponsor_id');
    }

    public function referrals(): HasMany
    {
        return $this->hasMany(self::class, 'sponsor_id');
    }

    public function treeNode(): HasOne
    {
        return $this->hasOne(MlmTree::class);
    }

    public function wallet(): HasOne
    {
        return $this->hasOne(Wallet::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasManyThrough(Transaction::class, Wallet::class);
    }

    public function commissions(): HasMany
    {
        return $this->hasMany(Commission::class, 'user_id');
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(CvPurchase::class);
    }

    public function activations(): HasMany
    {
        return $this->hasMany(CvActivation::class);
    }

    public function pins(): HasMany
    {
        return $this->hasMany(Pin::class, 'assigned_to');
    }

    public function kycDocuments(): HasMany
    {
        return $this->hasMany(KycDocument::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function withdrawalRequests(): HasMany
    {
        return $this->hasMany(WithdrawalRequest::class);
    }

    public function sponsorRelation(): HasOne
    {
        return $this->hasOne(Sponsor::class, 'sponsee_id');
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin' || $this->hasRole('admin');
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function totalDownline(): int
    {
        return MlmTree::query()->where('path', 'like', "%/{$this->id}/%")->count();
    }
}
