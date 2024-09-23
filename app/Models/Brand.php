<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Str;

class Brand extends Model
{
    use HasFactory;

    public $table = 'brands';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'name'
    ];
    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($model): void {
            $model->id = (string) \Str::uuid(); // Generate UUID on create
        });
    }

    public function products():HasMany
    {
        return $this->hasMany(Product::class);
    }
}
