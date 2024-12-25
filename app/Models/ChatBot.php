<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatBot extends Model
{
    use HasFactory;
    protected $fillable = [
        'device_id',
        'msg',
        'content_id',
        'type',
        'status',
    ];
    protected $casts = [
        'status' => 'boolean',
    ];



    // Relationship with Device
    public function device()
    {
    
        return $this->belongsTo(Device::class);
    }

    // Relationship with Content
    public function content()
    {
        return $this->belongsTo(Content::class);
    }
    public function user_device()
{
    return $this->belongsTo(Device::class, 'device_id');
}
public function user_device_user()
{
    return $this->belongsTo(Device::class,'device_id')->where('user_id', auth()->id());
}
}
