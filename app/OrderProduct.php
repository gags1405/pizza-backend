<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OrderProduct extends Model
{
    protected $table = 'order_products';
    public $timestamps = false;

    public function detail(){
    	return $this->belongsTo('App\Products', 'product_id', 'id');
    }

}
