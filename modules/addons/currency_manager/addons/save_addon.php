<?php

/**
 * Module for Manage Multi Currency Save Addon
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

// if (isset($_POST['addon_submit'])) {
    if (isset($_POST['addon_activation'])) {

        $deleteNotChecked = Capsule::table('mod_currency_manager_objects')
            ->where('object_type', '=', 'addon')
            ->whereNotIn('object_id', $_POST['addon_activation'])
            ->delete();

        foreach ($_POST['addon_activation'] as $addon_activationkey => $addon_activationvalue) {
            $usdPost = 'Addon_usdValue-'.$addon_activationvalue;
            $euroPost = 'Addon_euroValue-'.$addon_activationvalue;

            if ($_POST[$usdPost]) {
                $deleteCUR = Capsule::table('mod_currency_manager_objects')
                    ->where('object_type', '=', 'addon')
                    ->where('object_id', '=', $addon_activationvalue)
                    ->delete();
                $insertCUR = Capsule::table('mod_currency_manager_objects')->insert(
                    [
                        'object_type' => 'addon',
                        'object_id' => $addon_activationvalue,
                        'object_price' => $_POST[$usdPost],
                        'object_currency' => 'usd',
                        'created_at' => $now,
                    ]
                );
            } elseif ($_POST[$euroPost]) {
                $deleteCUR = Capsule::table('mod_currency_manager_objects')
                    ->where('object_type', '=', 'addon')
                    ->where('object_id', '=', $addon_activationvalue)
                    ->delete();
                $insertCUR = Capsule::table('mod_currency_manager_objects')->insert(
                    [
                        'object_type' => 'addon', 
                        'object_id' => $addon_activationvalue,
                        'object_price' => $_POST[$euroPost],
                        'object_currency' => 'euro',
                        'created_at' => $now,
                    ]
                );
            }
        }

        if ($insertCUR) {
            echo "<span style='color: green'>Addon Prices Saved Successfully</span>";
            // header("Location: ".$system_url.$GLOBALS['customadminpath']."/addonmodules.php?module=currency_manager&tab=addons&mess=InsertDone");
            // exit;
        } else {
            echo "<span style='color: red'>Addon Prices Save Error!</span>";
            // header("Location: ".$system_url.$GLOBALS['customadminpath']."/addonmodules.php?module=currency_manager&tab=addons&mess=InsertFaild");
            // exit;
        }
    }
// }

?>