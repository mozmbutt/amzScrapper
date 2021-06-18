<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exports\UsersExport;
use App\HafeezCenter;
use App\Product;
use App\Imports\UsersImport;
use App\ProductDescription;
use Maatwebsite\Excel\Facades\Excel;
use simple_html_dom;

require 'simple_html_dom.php';

ini_set('max_execution_time', '0'); // for infinite time of execution
ini_set('memory_limit', '-1');
class MyController extends Controller
{

    /**
     * @return \Illuminate\Support\Collection
     */
    public function importExportView()
    {
        return view('import');
    }
    public function products()
    {
        $products = ProductDescription::where('status', 'scraped')->paginate(10);
        return view('products', compact('products', json_decode($products, true)));
    }
    public function index()
    {
        $data = Product::where('item_name', '!=', null)->paginate(10);
        return view('welcome', compact('data'));
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function import()
    {
        Excel::import(new UsersImport, request()->file('file'));
        return back();
    }

    public function updateDB()
    {
        $result = ProductDescription::where('title', NULL)->where('image_link', NULL)->where('status', NULL)->get();
        foreach ($result as $row) {
            $row->delete();
        }
    }

    public function getProductURL()
    {
        $products = Product::where('amz_link', NULL)->get();
        $x = Product::where('amz_link', NULL)->count();
        $y = Product::where('amz_link', '!=', NULL)->count();
        dd('fill = ' . $y . ' empty = ' . $x);
        // $products = Product::all();

        foreach ($products as $tbl_product) {
            $item_name = str_replace(' ', '+', $tbl_product->item_name);
            $url = 'https://www.amazon.com/s?k=' . $item_name;

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

            $href_exp = '/<a class="a-link-normal a-text-normal" href="([^"]+)"(.*?)/';
            preg_match_all($href_exp, $html, $matches);
            $all_products = $matches[0];
            if (count($all_products) > 3) {
                $top_four_products = array();
                for ($link = 0; $link < 4; $link++) {
                    $top_four_products[] = $all_products[$link];
                }
                $sponsered_products = array();
                foreach ($top_four_products as $product) {
                    $temp = explode('href=', $product);
                    $temp_url = str_replace('"', '', $temp[1]);
                    $sponsered_products[] = 'https://www.amazon.com' . $temp_url;
                }

                $encoded_sponsered_products = json_encode($sponsered_products);
                $table = Product::findOrFail($tbl_product->id);
                $table->amz_link = $encoded_sponsered_products;
                $table->update();
                echo $tbl_product->id . ' Updated </br>';
            } else {
                $table = Product::findOrFail($tbl_product->id);
                $table->amz_link = NULL;
                $table->update();
                echo $tbl_product->id . ' Set Nulled </br>';
            }
            $all_products = array();
            $top_four_products = array();
            $encoded_sponsered_products = array();
            $sponsered_products = array();
        }
    }

    function amazon()
    {
        $products = Product::where('amz_link', '!=', NULL)->where('status', NULL)->take(100)->get();
        if (count($products) > 0) {

            foreach ($products as $product) {
                $urls = json_decode($product->amz_link);
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

                    $image = '/<img(.*?)src="([^"]+)(.*?)id="landingImage"/';
                    preg_match_all($image, $html, $img);
                    $product_image = NULL;
                    if ($img[3]) {
                        $full_tag = $img[3][0];
                        $temp = explode('data-old-hires=', $full_tag);
                        $temp = explode(' onload=', $temp[1]);
                        $product_image = str_replace('"', '', $temp[0]);
                    }


                    $title_exp = '/<span id="productTitle" class="a-size-large product-title-word-break">([^"]+)/';
                    preg_match_all($title_exp, $html, $title);
                    $product_title = NULL;
                    if ($title[1]) {
                        $temp = $title[1][0];
                        $temp = explode('</span>', $temp);
                        $product_title = trim($temp[0], "\n");
                    }


                    $disc_exp = '/<div id="feature-bullets" class="a-section a-spacing-medium a-spacing-top-small">(.*?)<\/div>/s';
                    preg_match_all($disc_exp, $html, $disc_raw);
                    $discription = NULL;
                    if ($disc_raw[0]) {
                        $raw_html = $disc_raw[0][0];
                        $disc_points_exp = '/<li><span class="a-list-item">[\n](.*?)[\n][\n]<\/span><\/li>/';
                        preg_match_all($disc_points_exp, $raw_html, $disc_points);
                        $discription = $disc_points[1];
                        $discription = json_encode($discription);
                    }


                    $details_exp = '/<div id="detailBullets_feature_div">(.*?)<\/div>/s';
                    preg_match_all($details_exp, $html, $details_raw);
                    $product_detail = NULL;
                    if ($details_raw[1]) {
                        $temp = $details_raw[1][0];
                        $temp = str_replace("\n", "", $temp);
                        $html = str_get_html($temp);
                        foreach ($html->find('li') as $element) {
                            $content[] = $element->innertext;
                        }
                        foreach ($content as $key => $value) {
                            $temp = str_replace('<span class="a-list-item"><span class="a-text-bold">', '', $value);
                            $temp = str_replace('</span>', '', $temp);
                            $temp = str_replace('<span>', '', $temp);
                            $content[$key] = $temp;
                        }
                        $product_detail = json_encode($content);
                    }


                    $product_desc = new ProductDescription();
                    $product_desc->product_id = $product->id;
                    $product_desc->title = $product_title;
                    $product_desc->image_link = $product_image;
                    $product_desc->description = $discription;
                    $product_desc->details = $product_detail;
                    $product_desc->url = $url;
                    $product_desc->save();
                }
                $product->status = 'scraped';
                $product->update();
            }
            echo 'sleep for 10 sec';
            sleep(10);
            echo 'continue exec....';
            $this->amazon();
        } else {
            echo 'all scrapped';
        }
    }

    function singleURL()
    {
        $products = ProductDescription::where('title', NULL)
            ->where('status', NULL)
            ->take(100)
            ->get();

        if (count($products) > 0) {

            foreach ($products as $product) {
                $url = $product->url;
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

                $image = '/<img(.*?)src="([^"]+)(.*?)id="landingImage"/';
                preg_match_all($image, $html, $img);
                $product_image = NULL;
                if ($img[3]) {
                    $full_tag = $img[3][0];
                    $temp = explode('data-old-hires=', $full_tag);
                    $temp = explode(' onload=', $temp[1]);
                    $product_image = str_replace('"', '', $temp[0]);
                }


                $title_exp = '/<span id="productTitle" class="a-size-large product-title-word-break">([^"]+)/';
                preg_match_all($title_exp, $html, $title);
                $product_title = NULL;
                if ($title[1]) {
                    $temp = $title[1][0];
                    $temp = explode('</span>', $temp);
                    $product_title = trim($temp[0], "\n");
                }


                $disc_exp = '/<div id="feature-bullets" class="a-section a-spacing-medium a-spacing-top-small">(.*?)<\/div>/s';
                preg_match_all($disc_exp, $html, $disc_raw);
                $discription = NULL;
                if ($disc_raw[0]) {
                    $raw_html = $disc_raw[0][0];
                    $disc_points_exp = '/<li><span class="a-list-item">[\n](.*?)[\n][\n]<\/span><\/li>/';
                    preg_match_all($disc_points_exp, $raw_html, $disc_points);
                    $discription = $disc_points[1];
                    $discription = json_encode($discription);
                }


                $details_exp = '/<div id="detailBullets_feature_div">(.*?)<\/div>/s';
                preg_match_all($details_exp, $html, $details_raw);
                $product_detail = NULL;
                if ($details_raw[1]) {
                    $temp = $details_raw[1][0];
                    $temp = str_replace("\n", "", $temp);
                    $html = str_get_html($temp);
                    foreach ($html->find('li') as $element) {
                        $content[] = $element->innertext;
                    }
                    foreach ($content as $key => $value) {
                        $temp = str_replace('<span class="a-list-item"><span class="a-text-bold">', '', $value);
                        $temp = str_replace('</span>', '', $temp);
                        $temp = str_replace('<span>', '', $temp);
                        $content[$key] = $temp;
                    }
                    $product_detail = json_encode($content);
                }

                $product->title = $product_title;
                $product->image_link = $product_image;
                $product->description = $discription;
                $product->details = $product_detail;
                $product->update();

                if ($product->title != NULL) {
                    $product->status = 'scrapped';
                }

                $product->update();
            }

            echo 'sleep for 10 sec </br>';
            sleep(10);
            echo 'continue exec.... </br>';
            $this->singleURL();
        } else {
            echo 'all scrapped </br>';
        }
    }


}
