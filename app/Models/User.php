<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;


class User extends Authenticatable
// implements MustVerifyEmail
{
    use HasFactory, Notifiable,HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }




    public function devices()
    {
        return $this->hasMany(Device::class);
    }

    // Relationship with Contents
    public function contents()
    {
        return $this->hasMany(Content::class);
    }


//     public function activateFreePlan(): void
// {
//     $freePlan = Plan::where('is_free', true)->first();

//     if ($freePlan) {
//         $this->subscriptions()->create([
//             'device_id' => null, // يمكن تغييره حسب احتياجاتك
//             'plan_id' => $freePlan->id,
//             'start_date' => now(),
//         ]);
//     }
// }
public function subscriptions()
{
    return $this->hasMany(Subscription::class);
}
}
