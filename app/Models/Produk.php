<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Produk extends Model
{
    protected $guarded;

    public function kategori(){
        return $this->belongsTo(Kategori::class);
    }
    public function detail(){
        return $this->hasMany(OrderDetail::class);
    }

}
