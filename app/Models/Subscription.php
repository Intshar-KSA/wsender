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
        'payment_status',
        'payment_method',
        'receipt_url',
        'transaction_id',
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








    // // دالة لحساب تاريخ انتهاء الاشتراك
    // public function getExpirationDate()
    // {
    //     return $this->start_date
    //     ? $this->start_date->copy()->addHours($this->plan->hours ?? 0)
    //     : null;
    // }

    public function getExpirationDate()
{
    // إذا كان تاريخ البدء غير موجود
    if (!$this->start_date) {
        return null;
    }

    $expirationDate = $this->start_date->copy(); // نسخ تاريخ البداية

    // إذا كانت الخطة مجانية، يتم الحساب بالساعات
    if ($this->plan->is_free) {
        if (!empty($this->plan->hours)) {
            $expirationDate->addHours($this->plan->hours);
        }
    } else {
        // إذا كانت الخطة مدفوعة، يتم الحساب بالأيام
        if (!empty($this->plan->number_of_days)) {
            $expirationDate->addDays($this->plan->number_of_days);
        }
    }

    return $expirationDate;
}




    // دالة لمعرفة إذا كان الاشتراك منتهيًا
    public function isExpired(): bool
    {
        $expirationDate = $this->getExpirationDate(); // تاريخ الانتهاء المحسوب
        return $expirationDate ? now()->greaterThan($expirationDate) : true; // انتهى إذا كان التاريخ الحالي أكبر
    }
}
