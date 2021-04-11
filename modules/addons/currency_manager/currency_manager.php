<?php

/**
 * Module for Manage Multi Currency 
 * 
 * PHP version 5.6.x | 7.x | 8.x
 * 
 * @category  Addons
 * @package   Whmcs
 * @author    Pejman Kheyri <pejmankheyri@gmail.com>
 * @copyright 2021 All rights reserved.
 */

use Illuminate\Database\Capsule\Manager as Capsule;

/**
 * Module Config function
 * 
 * @return array
 */
function Currency_Manager_config()
{
    $activesetting = Capsule::table('tbladdonmodules')->where(
        [
            ['module', '=', 'currency_manager'],
        ]
    )->get();

    foreach ($activesetting as $setkey => $setval) {
        if ($setval->setting == 'UserTypeClientGroup') {
            $activeUserTypefield = $setval->value;
        }
    }

    $getclientgroups = Capsule::table('tblclientgroups')->get();

    foreach ($getclientgroups as $clientgroup) {
        if ($clientgroup->id == $activeUserTypefield) {
            $default_usertype = $clientgroup->id;
        } 
        $UserTypeClientGroup[$clientgroup->id] = $clientgroup->groupname;
    }

    $configarray = array(
        'name' => 'Currency Manager',
        'version' => '1.0.0',
        'author' => 'pejman kheyri',
        'description' => 'Multiple Currency Manager Module',
        'language' => 'english',
        "fields" => array(
            "UserTypeClientGroup" => array (
                "FriendlyName" => 'Choose reseller Group', 
                "Type" => "dropdown", 
                "Options" => $UserTypeClientGroup,
                "Description" => "<a href='configclientgroups.php' target='_blank'>Client Group</a>",
                "Default" => $default_usertype
            )
        )
    );
    return $configarray;
}

/**
 * Module Activate Function
 * 
 * @return void
 */
function Currency_Manager_activate()
{

    try {

        $create_setting = "CREATE TABLE IF NOT EXISTS 
        `mod_currency_manager_setting` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `setting_keyword` varchar(256) NOT NULL,
            `setting_value` varchar(256) NOT NULL,
            `setting_symbol` varchar(1) NULL,
            `is_active` INT(11) NOT NULL,
            PRIMARY KEY (`id`)
        ) 
        ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;";
        $setting_result = full_query($create_setting);

        $create_object = "CREATE TABLE IF NOT EXISTS 
        `mod_currency_manager_objects` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `object_type` varchar(64) NOT NULL,
            `object_id` varchar(64) NOT NULL,
            `object_price` INT(11) NOT NULL,
            `object_currency` varchar(4) NOT NULL,
            `created_at` timestamp NOT NULL default CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        ) 
        ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;";
        $object_result = full_query($create_object);
    } catch (\Exception $e) {
        echo "Unable to create mod_currency_manager Tables: {$e->getMessage()}";
    }
}

/**
 * Module Deactivate function
 * 
 * @return void
 */
function Currency_Manager_deactivate()
{
    $query = "DROP TABLE `mod_currency_manager_setting`";
    $result = full_query($query);
}

/**
 * Module Output function
 * 
 * @param variables $vars 
 * 
 * @return string
 */
function Currency_Manager_output($vars)
{
    try {
        $modulelink = $vars['modulelink'];
        $mess = $_GET['mess'];

        if ($mess == "InsertDone") {
            echo Currency_Manager_Insert_Success_mess();
        }

        if ($mess == "InsertFaild") {
            echo Currency_Manager_Insert_Failed_mess();
        }

        $url = Capsule::table('tblconfiguration')
            ->select('value')
            ->where('setting', '=', 'SystemURL')
            ->first();
        $system_url = $url->value;

        $tmpval = Capsule::table('tblconfiguration')
            ->select('value')
            ->where('setting', '=', 'Template')
            ->first();
        $template = $tmpval->value;

        $loaderFile = $system_url.'templates/'.$template.'/img/currency_manager/loader.gif';

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

        $configuration = Capsule::table('mod_currency_manager_setting')
            ->select('setting_keyword', 'setting_value', 'setting_symbol', 'is_active')
            ->get();

        foreach ($configuration as $config_key => $config_value) {
            if ($config_value->setting_keyword == 'usd_value') {
                $usd_value = $config_value->setting_value;
                $usd_symbol = $config_value->setting_symbol;
                $usd_isactive = $config_value->is_active;
            }
            if ($config_value->setting_keyword == 'euro_value') {
                $euro_value = $config_value->setting_value;
                $euro_symbol = $config_value->setting_symbol;
                $euro_isactive = $config_value->is_active;
            }
            if ($config_value->setting_keyword == 'pound_value') {
                $pound_value = $config_value->setting_value;
                $pound_symbol = $config_value->setting_symbol;
                $pound_isactive = $config_value->is_active;
            }
        }

        $tab = $_GET['tab'];
        echo '
        <link href="'.$system_url.'templates/'.$template.'/css/currency_manager/styles.css" rel="stylesheet" />

		<ul class="nav nav-tabs client-tabs" id="tabs">
            <li class="tab"><a class="clientTab-6" '.(($tab == "configurable_options") ? "id='a_tab'" : "").'" href="addonmodules.php?module=currency_manager&amp;tab=configurable_options">configurable_options</a></li>
            <li class="tab"><a class="clientTab-5" '.(($tab == "addons") ? "id='a_tab'" : "").'" href="addonmodules.php?module=currency_manager&amp;tab=addons">addons</a></li>
            <li class="tab"><a class="clientTab-4" '.(($tab == "domainaddons") ? "id='a_tab'" : "").'" href="addonmodules.php?module=currency_manager&amp;tab=domainaddons">domain_addons</a></li>
            <li class="tab"><a class="clientTab-3" '.(($tab == "domains") ? "id='a_tab'" : "").'" href="addonmodules.php?module=currency_manager&amp;tab=domains">domains</a></li>
            <li class="tab"><a class="clientTab-2" '.(($tab == "products") ? "id='a_tab'" : "").'" href="addonmodules.php?module=currency_manager&tab=products">products</a></li>
            <li class="tab"><a class="clientTab-1" '.((($tab == "configuration") || ($tab == "")) ? "id='a_tab'" : "").'" href="addonmodules.php?module=currency_manager&tab=configuration">configuration</a></li>
		</ul>
		<div class="clear"></div>';

        echo '
        <!--<script src="'.$system_url.'templates/'.$template.'/js/currency_manager/datatables.js"></script>
        <link rel="stylesheet" href="'.$system_url.'templates/'.$template.'/css/currency_manager/datatable.css" type="text/css">
        <link rel="stylesheet" href="'.$system_url.'templates/'.$template.'/css/currency_manager/themeroller.css" type="text/css">
        <script type="text/javascript">
            $(document).ready(function(){
                $(".datatable").dataTable();
            });
        </script>-->';

        date_default_timezone_set('Asia/Tehran');
        $now = date("Y-m-d H:i:s", time());

        if (!isset($tab) || $tab == "configuration") {

            include "tabs/configuration.php";

        } elseif ($tab == "products") {

            include "tabs/products.php";

        } elseif ($tab == "domains") {

            include "tabs/domains.php";
       
        } elseif ($tab == "domainaddons") {

            include "tabs/domainaddons.php";

        } elseif ($tab == "addons") {

            include "tabs/addons.php";

        } elseif ($tab == "configurable_options") {

            include "tabs/configurable.php";

        }
    } catch (\Exception $e) {
        echo "Unable to load output data`s: {$e->getMessage()}";
    }
}

/**
 * Currency Manager insert success message
 * 
 * @return void
 */
function Currency_Manager_Insert_Success_mess()
{
    echo '<div class="successbox">
		<strong>
		<span class="title">Success</span>
		</strong><br>Currency Setting Saved Successfully.</div>';
}

/**
 * Currency Manager insert failed message
 * 
 * @return void
 */
function Currency_Manager_Insert_Failed_mess()
{
    echo '<div class="errorbox">
		<strong>
		<span class="title">Error</span>
		</strong><br>Currency Setting Saving Failed !</div>';
}

?>