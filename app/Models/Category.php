<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Str;

class Category extends Model
{
    use HasFactory;

    public $table = 'categories';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'name',
        'image'
    ];
    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->id = (string) \Str::uuid(); // Generate UUID on create
        });
    }
}
