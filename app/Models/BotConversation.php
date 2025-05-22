<?php

// app/Models/BotConversation.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BotConversation extends Model
{
    protected $fillable = ['chat_id', 'last_greeted_at'];

    protected $dates = ['last_greeted_at'];
}
