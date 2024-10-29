<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Content extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'title',
        'des',
        'file',
        'file_type',
    ];

    // Relationship with User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relationship with Campaigns
    public function campaigns()
    {
        return $this->hasMany(Campaign::class);
    }

    // Relationship with ChatBot
    public function chatBots()
    {
        return $this->hasMany(ChatBot::class);
    }
}
