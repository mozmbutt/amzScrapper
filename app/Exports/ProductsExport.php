<?php

namespace App\Exports;

use App\ProductDescription;
use Maatwebsite\Excel\Concerns\FromCollection;

class ProductsExport implements FromCollection
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return ProductDescription::select('title', 'image_link', 'url', 'details', 'description')->get();
    }
}
