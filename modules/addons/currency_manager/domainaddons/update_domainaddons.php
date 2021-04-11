<?php

/**
 * Module for Manage Multi Currency Update Domain Addons
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

$select_domainaddons = Capsule::table('mod_currency_manager_objects')
    ->select('object_id', 'object_price', 'object_currency')
    ->where('object_type', '=', 'domainaddons')
    ->get();

foreach ($select_domainaddons as $select_domainaddons_key => $select_domainaddons_value) {

    $object_price = $select_domainaddons_value->object_price;
    $object_currency = $select_domainaddons_value->object_currency;

    if ($object_currency == "usd") {
        $domainaddon_price = $usd_value * $object_price;
    } elseif ($object_currency == "euro") {
        $domainaddon_price = $euro_value * $object_price;
    }

    switch ($select_domainaddons_value->object_id) {
    case 'DNS_Management':
        $price_column = 'msetupfee';
        break;
    case 'Email_Forwarding':
        $price_column = 'qsetupfee';
        break;                            
    case 'ID_Protection':
        $price_column = 'ssetupfee';
        break;
    }

    $update_domainaddon = Capsule::table('tblpricing as p')
        ->join('tblcurrencies as c', 'c.id', '=', 'p.currency')
        ->where('p.type', '=', 'domainaddons')
        ->where('c.default', '=', 1)
        ->update(
            [
                $price_column => $domainaddon_price
            ]
        );
}

if (($update_domainaddon == 0) || ($update_domainaddon == 1)) {
    echo "<span style='color: green'>Domain Addons Updated Successfully</span>";
    // header("Location: ".$system_url.$GLOBALS['customadminpath']."/addonmodules.php?module=currency_manager&tab=domainaddons&mess=InsertDone");
    // exit;
} else {
    echo "<span style='color: red'>Domain Addons Update Error!</span>";
    // header("Location: ".$system_url.$GLOBALS['customadminpath']."/addonmodules.php?module=currency_manager&tab=domainaddons&mess=InsertFaild");
    // exit;
}

?>