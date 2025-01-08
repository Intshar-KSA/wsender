<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuickSend extends Model
{
    use HasFactory;

    protected $fillable = [
        'profile_id',
        'message_text',
        'phone_numbers',
        'image',
        'timeout_from',
        'timeout_to',
        'base64',
        'file_name',
        'status',
        'mass_posting_id'
    ];
}
