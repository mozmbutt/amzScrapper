<?php

namespace App\Exports;

use App\HafeezCenter;
use Maatwebsite\Excel\Concerns\FromCollection;

class ShopsExport implements FromCollection
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return HafeezCenter::select('title' , 'phone' , 'address' , 'type')->get();
    }
}
