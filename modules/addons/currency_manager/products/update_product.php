<?php

/**
 * Module for Manage Multi Currency Update Product
 * 
 * PHP version 5.6.x | 7.x | 8.x
 * 
 * @category  Addons
 * @package   Whmcs
 * @author    Pejman Kheyri <pejmankheyri@gmail.com>
 * @copyright 2021 All rights reserved.
 */

require_once "../../../../init.php";

use Illuminate\Database\Capsule\Manager as Capsule;

$url = Capsule::table('tblconfiguration')
    ->select('value')
    ->where('setting', '=', 'SystemURL')
    ->first();
$system_url = $url->value;

$configuration = Capsule::table('mod_currency_manager_setting')
    ->select('setting_keyword', 'setting_value', 'setting_symbol', 'is_active')
    ->get();

foreach ($configuration as $config_key => $config_value) {
    if ($config_value->setting_keyword == 'usd_value') {
        $usd_value = $config_value->setting_value;
    }
    if ($config_value->setting_keyword == 'euro_value') {
        $euro_value = $config_value->setting_value;
    }
    if ($config_value->setting_keyword == 'pound_value') {
        $pound_value = $config_value->setting_value;
    }
}

$select_products = Capsule::table('mod_currency_manager_objects')
    ->select('object_id', 'object_price', 'object_currency')
    ->where('object_type', '=', 'product')
    ->get();

foreach ($select_products as $select_products_key => $select_products_value) {

    $object_id_array = explode('_', $select_products_value->object_id);

    $product_id = $object_id_array[0];
    $product_period = $object_id_array[1];
    $product_type = $object_id_array[2];

    $object_price = $select_products_value->object_price;
    $object_currency = $select_products_value->object_currency;

    if ($object_currency == "usd") {
        $product_price = $usd_value * $object_price;
    } elseif ($object_currency == "euro") {
        $product_price = $euro_value * $object_price;
    }

    if ($product_type == "Setup") {
        switch ($product_period) {
        case 'monthly':
            $price_column = 'msetupfee';
            break;
        case 'quarterly':
            $price_column = 'qsetupfee';
            break;                            
        case 'semiannually':
            $price_column = 'ssetupfee';
            break;                            
        case 'annually':
            $price_column = 'asetupfee';
            break;
        case 'biennially':
            $price_column = 'bsetupfee';
            break;
        }
    } elseif ($product_type == "Price") {
        $price_column = $product_period;
    }

    $update_product = Capsule::table('tblpricing as p')
        ->join('tblcurrencies as c', 'c.id', '=', 'p.currency')
        ->where('p.type', '=', 'product')
        ->where('c.default', '=', 1)
        ->where('p.relid', '=', $product_id)
        ->update(
            [
                $price_column => $product_price
            ]
        );
}

if (($update_product == 0) || ($update_product == 1)) {
    echo "<span style='color: green'>Products Updated Successfully</span>";
    // header("Location: ".$system_url.$GLOBALS['customadminpath']."/addonmodules.php?module=currency_manager&tab=configuration&mess=InsertDone");
    // exit;
} else {
    echo "<span style='color: red'>Products Update Error!</span>";
    // header("Location: ".$system_url.$GLOBALS['customadminpath']."/addonmodules.php?module=currency_manager&tab=configuration&mess=InsertFaild");
    // exit;
}

?>