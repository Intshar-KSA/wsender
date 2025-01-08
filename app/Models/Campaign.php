<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
    use HasFactory;

    protected $fillable = [
        'device_id',
        'mass_prsting_id',
        'content_id',
        'contact_cat_ids', // حقل جديد لتخزين معرفات المجموعات
        'message_every',
        'starting_time',
        'allowed_period_from',
        'allowed_period_to',
        'status',
    ];

    protected $casts = [
        'contact_cat_ids' => 'array',
    ];



    // Relationship with Device
    public function device()
    {
        return $this->belongsTo(Device::class);
    }

    public function user_device()
    {
        return $this->belongsTo(Device::class, 'device_id')->where('user_id', auth()->id());
    }
//     public function user_device()
// {
//     return $this->belongsTo(Device::class, 'device_id');
// }


    // Relationship with Content
    public function content()
    {
        return $this->belongsTo(Content::class, 'content_id')->where('user_id', auth()->id());
    }

    public function contactCats()
{
    return $this->belongsToMany(ContactCat::class, 'campaign_contact_cat', 'campaign_id', 'contact_cat_id');
}


    // // Relationships with Contacts
    // public function fromContact()
    // {
    //     return $this->belongsTo(Contact::class, 'from_contact_id');
    // }

    // public function toContact()
    // {
    //     return $this->belongsTo(Contact::class, 'to_contact_id');
    // }
}
