<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'number_of_days',
        'price',
        'is_free',
        'hours',
    ];


    protected $casts = [
        'price' => 'float',
        'number_of_days' => 'integer',
        'is_free' => 'boolean',
    ];


    public static function boot()
{
    parent::boot();

    static::saving(function ($plan) {
        if ($plan->price < 0) {
            throw new \Exception("Price must be non-negative");
        }
        if ($plan->number_of_days < 0) {
            throw new \Exception("Number of days must be non-negative");
        }
    });
}


}
