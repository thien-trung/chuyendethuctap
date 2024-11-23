<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    protected $fillable = [
        'user_id', 'firstname', 'lastname', 'address', 'city', 'country', 'zip', 'telephone', 'address_id'
];
    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

        // Giả sử một đơn hàng có nhiều sản phẩm
        public function products()
        {
            return $this->hasMany('App\Models\Product');
        }

}
