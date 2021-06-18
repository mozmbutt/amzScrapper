<?php

namespace App\Http\Controllers;

use App\Exports\ProductsExport;
use App\ProductDescription;
use Illuminate\Http\Request;
use App\HafeezCenter;
use Maatwebsite\Excel\Facades\Excel;

class ProductDescriptionController extends Controller
{
    public function export()
    {
        return Excel::download(new ProductsExport, 'amazon_products.xlsx');
    }
}
