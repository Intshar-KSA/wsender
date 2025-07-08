<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    use HasFactory;

    protected $fillable = [
        'nickname',
        'profile_id',
        'webhook_url',
        'user_id',
        'status',
    ];
    protected $casts = [
    'profile_id' => 'string',
];


    protected $appends = ['extra_data'];

    // خاصية إضافية لتحميل البيانات الإضافية
    public function getExtraDataAttribute()
    {
        // استدعاء API مرة واحدة لتحميل البيانات
        $apiService = app(\App\Services\ExternalApiService::class);
        $profiles = collect($apiService->getProfiles());

        // البحث عن البيانات المطابقة بناءً على profile_id
        return $profiles->firstWhere('profile_id', $this->profile_id) ?? [];
    }

    // Relationship with User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relationship with Subscriptions
    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
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

    public function statuses()
    {
        return $this->belongsToMany(Status::class, 'device_status', 'device_id', 'status_id');
    }

    protected static function booted()
    {
        static::addGlobalScope('current_user_devices', function (Builder $builder) {
            $builder->where('user_id', auth()->id());
        });
    }
}
