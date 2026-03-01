<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemExampleFile extends Model
{
    protected $fillable = [
        'item_id','user_id','original_name','path','mime','size_bytes'
    ];
}