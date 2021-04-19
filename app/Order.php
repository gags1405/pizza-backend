<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $table = 'orders';
    public $timestamps = false;

    /**
     * Get all of the products
     */
    public function products()
    {
        return $this->hasMany('App\OrderProduct', 'product_id', 'id');
    }

     
}
