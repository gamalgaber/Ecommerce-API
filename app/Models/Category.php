<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Str;

class Category extends Model
{
    use HasFactory, SoftDeletes;

    public $table = 'categories';
    public $incrementing = false;
    protected $dates = ['deleted_at', 'created_at', 'updated_at'];
    protected $keyType = 'string';
    protected $fillable = [
        'name',
        'image'
    ];
    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($model): void {
            $model->id = (string) \Str::uuid(); // Generate UUID on create
        });
    }


    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
