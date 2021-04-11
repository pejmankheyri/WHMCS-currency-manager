<?php

/**
 * Module for Manage Multi Currency Save Configurations
 * 
 * PHP version 5.6.x | 7.x | 8.x
 * 
 * @category  Addons
 * @package   Whmcs
 * @author    Pejman Kheyri <pejmankheyri@gmail.com>
 * @copyright 2021 All rights reserved.
 */

require_once "../../../init.php";

use Illuminate\Database\Capsule\Manager as Capsule;

try{

    if ($_POST['usd_value'] || $_POST['euro_value'] || $_POST['pound_value']) {

        $url = Capsule::table('tblconfiguration')
            ->select('value')
            ->where('setting', '=', 'SystemURL')
            ->first();
        $system_url = $url->value;

        $currency_array = [
            'usd' => $_POST['usd_value'], 
            'euro' => $_POST['euro_value'],
            'pound' => $_POST['pound_value']
        ];

        if ($currency_array) {
            foreach ($currency_array as $currency_key => $currency_value) {
                $check_exists = Capsule::table('mod_currency_manager_setting')
                    ->select('id')
                    ->where('setting_keyword', '=', $currency_key.'_value')
                    ->first();

                switch($currency_key){
                case 'usd' :
                    $symbol = '$';
                    break;
                case 'euro' :
                    $symbol = '€';
                    break;
                case 'pound' :
                    $symbol = '£';
                    break;
                }

                if ($check_exists) {
                    $update[] = Capsule::table('mod_currency_manager_setting')
                        ->where('id', '=', $check_exists->id)
                        ->update(
                            [
                                'setting_value' => $currency_value
                            ]
                        );
                } else {
                    $insert[] = Capsule::table('mod_currency_manager_setting')->insert(
                        [
                            'setting_keyword' => $currency_key.'_value', 
                            'setting_value' => $currency_value, 
                            'setting_symbol' => $symbol, 
                            'is_active' => 1
                        ]
                    );                
                }            
            }

            if ($update || $insert) {
                header("Location: ".$system_url.$GLOBALS['customadminpath']."/addonmodules.php?module=currency_manager&mess=InsertDone");
                exit;
            } else {
                header("Location: ".$system_url.$GLOBALS['customadminpath']."/addonmodules.php?module=currency_manager&mess=InsertFaild");
                exit;
            }
        }    
    }
} catch (\Exception $e) {
    echo "Update_prices Hook Exception: {$e->getMessage()}";
}

?>