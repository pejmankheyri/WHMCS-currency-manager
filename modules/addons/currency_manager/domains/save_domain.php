<?php

/**
 * Module for Manage Multi Currency Save Domain
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

// if (isset($_POST['domain_submit'])) {
    if (isset($_POST['domain_activation'])) {

        $deleteNotChecked = Capsule::table('mod_currency_manager_objects')
            ->where('object_type', '=', 'domain')
            ->whereNotIn('object_id', $_POST['domain_activation'])
            ->delete();

        foreach ($_POST['domain_activation'] as $added_key => $added_value) {
            $usdPost = 'Domain_usdValue_'.$added_value;
            $euroPost = 'Domain_euroValue_'.$added_value;

            if ($_POST[$usdPost]) {
                $deleteCUR = Capsule::table('mod_currency_manager_objects')
                    ->where('object_type', '=', 'domain')
                    ->where('object_id', '=', $added_value)
                    ->delete();
                $insertCUR = Capsule::table('mod_currency_manager_objects')->insert(
                    [
                        'object_type' => 'domain',
                        'object_id' => $added_value,
                        'object_price' => $_POST[$usdPost],
                        'object_currency' => 'usd',
                        'created_at' => $now,
                    ]
                );
            } elseif ($_POST[$euroPost]) {
                $deleteCUR = Capsule::table('mod_currency_manager_objects')
                    ->where('object_type', '=', 'domain')
                    ->where('object_id', '=', $added_value)
                    ->delete();
                $insertCUR = Capsule::table('mod_currency_manager_objects')->insert(
                    [
                        'object_type' => 'domain', 
                        'object_id' => $added_value,
                        'object_price' => $_POST[$euroPost],
                        'object_currency' => 'euro',
                        'created_at' => $now,
                    ]
                );
            }
        }

        if ($insertCUR) {
            echo "<span style='color: green'>Domain Prices Saved Successfully</span>";
            // header("Location: ".$system_url.$GLOBALS['customadminpath']."/addonmodules.php?module=currency_manager&tab=domains&mess=InsertDone");
            // exit;
        } else {
            echo "<span style='color: red'>Domain Prices Save Error!</span>";
            // header("Location: ".$system_url.$GLOBALS['customadminpath']."/addonmodules.php?module=currency_manager&tab=domains&mess=InsertFaild");
            // exit;
        }
    }
// }

?>