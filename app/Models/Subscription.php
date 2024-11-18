<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'device_id',
        'plan_id',
        'start_date',
    ];
    protected $casts = [
        'start_date' => 'datetime',
    ];

    // علاقة الاشتراك مع الخطة
    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    // علاقة الاشتراك مع المستخدم
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // علاقة الاشتراك مع الجهاز
    public function device()
    {
        return $this->belongsTo(Device::class);
    }



    // دالة لحساب تاريخ انتهاء الاشتراك
    public function getExpirationDate()
    {
        return $this->start_date
        ? $this->start_date->copy()->addHours($this->plan->hours ?? 0)
        : null;
    }

    // دالة لمعرفة إذا كان الاشتراك منتهيًا
    public function isExpired(): bool
    {
        $expirationDate = $this->getExpirationDate(); // تاريخ الانتهاء المحسوب
        return $expirationDate ? now()->greaterThan($expirationDate) : true; // انتهى إذا كان التاريخ الحالي أكبر
    }
}
