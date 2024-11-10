<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'contact_cat_id',
        'name',
        'phone',
    ];

    /**
     * علاقة جهة الاتصال مع المستخدم.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * علاقة جهة الاتصال مع التصنيف.
     */
    public function contactCat()
    {
        return $this->belongsTo(ContactCat::class, 'contact_cat_id');
    }
}