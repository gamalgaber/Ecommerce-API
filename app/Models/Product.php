<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    use HasFactory;
    public $table = 'products';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'category_id',
        'brand_id',
        'name',
        'price',
        'discount',
        'amount',
        'is_available',
        'image',
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

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class, 'brand_id', 'id');
    }
}
