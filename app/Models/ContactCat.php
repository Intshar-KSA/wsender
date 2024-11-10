<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactCat extends Model
{
    use HasFactory;

    protected $table = 'contacts_cats';

    protected $fillable = [
        'name',
        'user_id', // تأكد من وجود هذا الحقل إذا كان مطلوبًا
    ];

    /**
     * علاقة التصنيفات مع المستخدم.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * علاقة التصنيفات مع جهات الاتصال.
     */
    public function contacts()
    {
        return $this->hasMany(Contact::class, 'contact_cat_id');
    }
}