<?php

/**
 * Module for Manage Multi Currency Save Domain Addons
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

date_default_timezone_set('Asia/Tehran');
$now = date("Y-m-d H:i:s", time());

if (isset($_POST['domainaddons_submit'])) {

    $deleteNotChecked = Capsule::table('mod_currency_manager_objects')
        ->where('object_type', '=', 'domainaddons')
        ->delete();

    $whois_proxy = array(
        'DNS_Management', 'Email_Forwarding', 'ID_Protection'
    );

    foreach ($whois_proxy as $whois_proxykey => $whois_proxyvalue) {
        $usdPost = 'DomainAddons_usdValue-'.$whois_proxyvalue;
        $euroPost = 'DomainAddons_euroValue-'.$whois_proxyvalue;

        if ($_POST[$usdPost]) {
            $deleteCUR = Capsule::table('mod_currency_manager_objects')
                ->where('object_type', '=', 'domainaddons')
                ->where('object_id', '=', $whois_proxyvalue)
                ->delete();
            $insertCUR = Capsule::table('mod_currency_manager_objects')->insert(
                [
                    'object_type' => 'domainaddons',
                    'object_id' => $whois_proxyvalue,
                    'object_price' => $_POST[$usdPost],
                    'object_currency' => 'usd',
                    'created_at' => $now,
                ]
            );
        } elseif ($_POST[$euroPost]) {
            $deleteCUR = Capsule::table('mod_currency_manager_objects')
                ->where('object_type', '=', 'domainaddons')
                ->where('object_id', '=', $whois_proxyvalue)
                ->delete();
            $insertCUR = Capsule::table('mod_currency_manager_objects')->insert(
                [
                    'object_type' => 'domainaddons', 
                    'object_id' => $whois_proxyvalue,
                    'object_price' => $_POST[$euroPost],
                    'object_currency' => 'euro',
                    'created_at' => $now,
                ]
            );
        }
    }

    if ($insertCUR) {
        header("Location: ".$system_url.$GLOBALS['customadminpath']."/addonmodules.php?module=currency_manager&tab=domainaddons&mess=InsertDone");
        exit;
    } else {
        header("Location: ".$system_url.$GLOBALS['customadminpath']."/addonmodules.php?module=currency_manager&tab=domainaddons&mess=InsertFaild");
        exit;
    }
}

?>