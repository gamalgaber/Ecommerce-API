<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Str;

class PersonalAccessToken extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'name', 'token', 'expires_at'];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($model): void {
            $model->id = (string) \Str::uuid(); // Generate UUID on create
        });
    }

    public function isExpired(): bool
    {
        return Carbon::now()->greaterThan($this->expires_at);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
