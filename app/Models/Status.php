<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Status extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'caption',
        'start_date',
        'end_date',
        'time',
        'last_run_at',
        'file_url',
        'is_active',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'time' => 'datetime:H:i',
        'last_run_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    // هل الحالة نشطة؟
    public function isActive(): bool
    {
        return $this->is_active && now()->between($this->start_date, $this->end_date);
    }

    // app/Models/Status.php

    public function devices()
    {
        return $this->belongsToMany(Device::class, 'device_status', 'status_id', 'device_id');
    }
}
