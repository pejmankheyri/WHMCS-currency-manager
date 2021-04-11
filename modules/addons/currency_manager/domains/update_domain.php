<?php

/**
 * Module for Manage Multi Currency Update Domain
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

$activesetting = Capsule::table('tbladdonmodules')->where(
    [
        ['module', '=', 'currency_manager'],
    ]
)->get();

foreach ($activesetting as $setkey => $setval) {
    if ($setval->setting == 'UserTypeClientGroup') {
        $activeResellerId = $setval->value;
    }
}

$select_domains = Capsule::table('mod_currency_manager_objects')
    ->select('object_id', 'object_price', 'object_currency')
    ->where('object_type', '=', 'domain')
    ->get();

foreach ($select_domains as $select_domains_key => $select_domains_value) {

    $object_id_array = explode('_', $select_domains_value->object_id);

    $domain_tld = $object_id_array[0];
    $domain_main_reseller = $object_id_array[1];
    $domain_register_renew = strtolower($object_id_array[2]);
    $domain_year = $object_id_array[3];

    $object_price = $select_domains_value->object_price;
    $object_currency = $select_domains_value->object_currency;

    if ($object_currency == "usd") {
        $domain_price = $usd_value * $object_price;
    } elseif ($object_currency == "euro") {
        $domain_price = $euro_value * $object_price;
    }

    if ($domain_main_reseller == 'Main') {
        $tsetupfee_value = 0;
    } elseif ($domain_main_reseller == 'Reseller') {
        $tsetupfee_value = $activeResellerId;
    }

    switch ($domain_year) {
    case 1:
        $price_column = 'msetupfee';
        break;
    case 2:
        $price_column = 'qsetupfee';
        break;                            
    case 3:
        $price_column = 'ssetupfee';
        break;                            
    case 4:
        $price_column = 'asetupfee';
        break;
    case 5:
        $price_column = 'bsetupfee';
        break;
    case 6:
        $price_column = 'monthly';
        break;
    case 7:
        $price_column = 'quarterly';
        break;                            
    case 8:
        $price_column = 'semiannually';
        break;                            
    case 9:
        $price_column = 'annually';
        break;
    case 10:
        $price_column = 'biennially';
        break;
    }

    $update_domain = Capsule::table('tblpricing as p')
        ->join('tblcurrencies as c', 'c.id', '=', 'p.currency')
        ->where('p.type', '=', 'domain'.$domain_register_renew)
        ->where('c.default', '=', 1)
        ->where('p.relid', '=', $domain_tld)
        ->where('p.tsetupfee', '=', $tsetupfee_value)
        ->update(
            [
                $price_column => $domain_price
            ]
        );

}

if (($update_domain == 0) || ($update_domain == 1)) {
    echo "<span style='color: green'>Domains Updated Successfully</span>";
    // header("Location: ".$system_url.$GLOBALS['customadminpath']."/addonmodules.php?module=currency_manager&tab=configuration&mess=InsertDone");
    // exit;
} else {
    echo "<span style='color: red'>Domains Update Error!</span>";
    // header("Location: ".$system_url.$GLOBALS['customadminpath']."/addonmodules.php?module=currency_manager&tab=configuration&mess=InsertFaild");
    // exit;
}

?>