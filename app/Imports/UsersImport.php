<?php

namespace App\Imports;

use App\Product;
use App\User;
use Maatwebsite\Excel\Concerns\ToModel;

class UsersImport implements ToModel
{
  /**
   * @param array $row
   *
   * @return \Illuminate\Database\Eloquent\Model|null
   */
  public function model(array $row)
  {
    return new Product[
      'barcode' => $row['1'],
      'item_name' => $row['2'],
      'pack_qty' => $row['3'],
      'quantity' =>  $row['4'],
      't_price' => $row['5'],
      'category' => '',
      'amz_link' => '',
    ]);
  }
}
