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
        'from_contact_id',
        'to_contact_id',
        'message_every',
        'last_phone',
        'starting_time',
        'allowed_period_from',
        'allowed_period_to',
        'status',
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

    // Relationship with Content
    public function content()
    {
        return $this->belongsTo(Content::class);
    }

    // Relationships with Contacts
    public function fromContact()
    {
        return $this->belongsTo(Contact::class, 'from_contact_id');
    }

    public function toContact()
    {
        return $this->belongsTo(Contact::class, 'to_contact_id');
    }
}
