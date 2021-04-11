<?php

/**
 * Module for Manage Multi Currency Save Product
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

//if (isset($_POST['product_submit'])) {
    if (isset($_POST['product_activation'])) {

        $deleteNotChecked = Capsule::table('mod_currency_manager_objects')
            ->where('object_type', '=', 'product')
            ->whereNotIn('object_id', $_POST['product_activation'])
            ->delete();

        foreach ($_POST['product_activation'] as $product_activationkey => $product_activationvalue) {
            $usdPost = 'Product_usdValue-'.$product_activationvalue;
            $euroPost = 'Product_euroValue-'.$product_activationvalue;

            if ($_POST[$usdPost]) {
                $deleteCUR = Capsule::table('mod_currency_manager_objects')
                    ->where('object_type', '=', 'product')
                    ->where('object_id', '=', $product_activationvalue)
                    ->delete();
                $insertCUR = Capsule::table('mod_currency_manager_objects')->insert(
                    [
                        'object_type' => 'product',
                        'object_id' => $product_activationvalue,
                        'object_price' => $_POST[$usdPost],
                        'object_currency' => 'usd',
                        'created_at' => $now,
                    ]
                );
            } elseif ($_POST[$euroPost]) {
                $deleteCUR = Capsule::table('mod_currency_manager_objects')
                    ->where('object_type', '=', 'product')
                    ->where('object_id', '=', $product_activationvalue)
                    ->delete();
                $insertCUR = Capsule::table('mod_currency_manager_objects')->insert(
                    [
                        'object_type' => 'product', 
                        'object_id' => $product_activationvalue,
                        'object_price' => $_POST[$euroPost],
                        'object_currency' => 'euro',
                        'created_at' => $now,
                    ]
                );
            }
        }

        if ($insertCUR) {
            echo "<span style='color: green'>Product Prices Saved Successfully</span>";
            // header("Location: ".$system_url.$GLOBALS['customadminpath']."/addonmodules.php?module=currency_manager&tab=products&mess=InsertDone");
            // exit;
        } else {
            echo "<span style='color: red'>Product Prices Save Error!</span>";
            // header("Location: ".$system_url.$GLOBALS['customadminpath']."/addonmodules.php?module=currency_manager&tab=products&mess=InsertFaild");
            // exit;
        }
    }
//}

?>