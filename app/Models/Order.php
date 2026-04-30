<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $guarded;

    public function detail(){
        return $this->hasMany(OrderDetail::class);
    }
    public function user(){
        return $this->belongsTo(User::class);
    }
}
