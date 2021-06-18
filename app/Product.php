<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Product extends Authenticatable
{
	protected $table = 'products';

     protected $fillable = [
        'barcode', 'item_name', 'pack_qty', 'quantity', 't_price', 'category', 'amz_link'
    ];

    public function description()
    {
        return $this->hasMany(ProductDescription::class);
    }
}
