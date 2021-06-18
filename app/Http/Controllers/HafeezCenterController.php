<?php

namespace App\Http\Controllers;

use App\Exports\ShopsExport;
use Illuminate\Http\Request;
use App\HafeezCenter;
use Maatwebsite\Excel\Facades\Excel;

class HafeezCenterController extends Controller
{
    public function scrap()
    {
        $urls = array();
        for ($i = 1; $i <= 6; $i++) {
            $urls[] = 'https://www.hafeezcenter.net/?page=' . $i;
        }

        foreach ($urls as $url) {
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_HTTPHEADER => array(
                    'Cookie: i18n-prefs=USD; session-id=134-8418886-3088714; session-id-time=2082787201l; sp-cdn="L5Z9:PK"'
                ),
            ));
            $response = curl_exec($curl);
            curl_close($curl);
            $html = $response;

            $title_exp = '/<div class="title">(.*?)<\/div>/s';

            preg_match_all($title_exp, $html, $title);

            $titles = $title[1];
            foreach ($titles as $key => $value) {
                if ($key < 20) {
                    $temp[$key] = trim($value, " ");
                    $temp[$key] = trim($temp[$key], "\n");
                    $temp[$key] = str_replace('<h1>', '', $temp[$key]);
                    $temp[$key] = str_replace('</h1>', '', $temp[$key]);
                    $temp[$key] = str_replace('                                ', '', $temp[$key]);
                }
            }

            $temp_title = $temp;
            $content_exp = '/<div class="content">(.*?)<\/div>/s';
            preg_match_all($content_exp, $html, $content);
            $contents = $content[1];
            foreach ($contents as $key => $value) {
                if ($key < 20) {
                    $temp_content[$key] = trim($value, " ");
                    $temp_content[$key] = trim($temp_content[$key], "\n");
                    $temp_content[$key] = str_replace('<divclass="row">', '', $temp_content[$key]);
                    $temp_content[$key] = str_replace('<divclass="col-md-6">', '', $temp_content[$key]);
                    $temp_content[$key] = str_replace('<spanclass="number"><aid="h1"onclick="show()"style="color:white;">ShowphoneNumber</a></span>', '', $temp_content[$key]);
                    $temp_content[$key] = str_replace('&emsp;', '', $temp_content[$key]);
                    $temp_content[$key] = explode('</p>', $temp_content[$key]);
                    foreach ($temp_content as $value) {
                        $details[$key]['address'] = str_replace('                                <p>', '', $value[0]);
                        $temp_phone = trim($value[1], " ");
                        $temp_phone = trim($temp_phone, "\n");
                        $temp_phone = str_replace('                                <p class="mail"><strong>Phone :</strong>', '', $temp_phone);
                        $details[$key]['phone'] = $temp_phone;
                        $temp_type = trim($value[2], " ");
                        $temp_type = trim($temp_type, "\n");
                        $temp_type = str_replace('                                <p><strong>Products :</strong>', '', $temp_type);
                        $details[$key]['type'] = $temp_type;
                    }
                }
            }

            foreach ($details as $key => $value) {
                $table = new HafeezCenter();
                $table->title = $temp_title[$key];
                $table->phone = $value['phone'];
                $table->address = $value['address'];
                $table->type = $value['type'];
                $table->save();
            }

            echo 'sleep for 10 sec </br>';
            sleep(10);
            echo 'continue exec.... </br>';

            $temp_title = NULL;
            $temp_phone = NULL;
            $temp_type = NULL;
            $temp = NULL;
            $details = array();
            $contents = array();
            $temp_content = NULL;
            $key = NULL;
            $value = NULL;
        }
    }

    public function export()
    {
        return Excel::download(new ShopsExport, 'hafeez_center_shops.xlsx');
    }
}
