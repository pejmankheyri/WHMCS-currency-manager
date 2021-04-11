<?php

/**
 * Module for Manage Multi Currency Save Configurable Options
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

// if (isset($_POST['configurable_submit'])) {
    if (isset($_POST['configurable_activation'])) {

        $deleteNotChecked = Capsule::table('mod_currency_manager_objects')
            ->where('object_type', '=', 'configurable')
            ->whereNotIn('object_id', $_POST['configurable_activation'])
            ->delete();

        foreach ($_POST['configurable_activation'] as $configurable_activationkey => $configurable_activationvalue) {
            $usdPost = 'Configurable_usdValue-'.$configurable_activationvalue;
            $euroPost = 'Configurable_euroValue-'.$configurable_activationvalue;

            if ($_POST[$usdPost]) {
                $deleteCUR = Capsule::table('mod_currency_manager_objects')
                    ->where('object_type', '=', 'configurable')
                    ->where('object_id', '=', $configurable_activationvalue)
                    ->delete();
                $insertCUR = Capsule::table('mod_currency_manager_objects')->insert(
                    [
                        'object_type' => 'configurable',
                        'object_id' => $configurable_activationvalue,
                        'object_price' => $_POST[$usdPost],
                        'object_currency' => 'usd',
                        'created_at' => $now,
                    ]
                );
            } elseif ($_POST[$euroPost]) {
                $deleteCUR = Capsule::table('mod_currency_manager_objects')
                    ->where('object_type', '=', 'configurable')
                    ->where('object_id', '=', $configurable_activationvalue)
                    ->delete();
                $insertCUR = Capsule::table('mod_currency_manager_objects')->insert(
                    [
                        'object_type' => 'configurable', 
                        'object_id' => $configurable_activationvalue,
                        'object_price' => $_POST[$euroPost],
                        'object_currency' => 'euro',
                        'created_at' => $now,
                    ]
                );
            }
        }

        if ($insertCUR) {
            echo "<span style='color: green'>Configurable Prices Saved Successfully</span>";
            // header("Location: ".$system_url.$GLOBALS['customadminpath']."/addonmodules.php?module=currency_manager&tab=configurable_options&mess=InsertDone");
            // exit;
        } else {
            echo "<span style='color: red'>Configurable Prices Save Error!</span>";
            // header("Location: ".$system_url.$GLOBALS['customadminpath']."/addonmodules.php?module=currency_manager&tab=configurable_options&mess=InsertFaild");
            // exit;
        }
    }
// }

?>