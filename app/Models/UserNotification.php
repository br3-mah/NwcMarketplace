<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserNotification extends Model
{
    protected $fillable = [
        'user_id',
        'order_number',
        'is_read',
    ];

    public static function countOrder($id)
    {
        return UserNotification::where('user_id','=',$id)->where('is_read','=',0)->get()->count();
    }

}
