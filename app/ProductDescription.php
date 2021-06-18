<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProductDescription extends Model
{
    protected $table = 'product_descriptions';

     protected $fillable = [
        'product_id', 'title', 'image_link', 'details', 'url'
    ];

    public function product()
    {
        return $this->belongsTo(property::class);
    }
}
