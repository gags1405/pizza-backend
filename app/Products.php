<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Products extends Model
{
    protected $table = 'products';
    public $timestamps = false;

    public function options() {
		return $this->hasMany('App\ProductOption','product_id');
	}
}
