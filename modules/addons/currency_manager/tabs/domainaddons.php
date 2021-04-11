<?php

/**
 * Module for Manage Multi Currency Domain Addons Tab
 * 
 * PHP version 5.6.x | 7.x | 8.x
 * 
 * @category  Addons
 * @package   Whmcs
 * @author    Pejman Kheyri <pejmankheyri@gmail.com>
 * @copyright 2021 All rights reserved.
 */

use Illuminate\Database\Capsule\Manager as Capsule; 

$active_objects = Capsule::table('mod_currency_manager_objects')
    ->select('object_id', 'object_price', 'object_currency')
    ->where('object_type', '=', 'domainaddons')
    ->get();

foreach ($active_objects as $active_objectskey => $active_objectsvalue) {
    $domainaddons_values[$active_objectsvalue->object_id][$active_objectsvalue->object_currency] = $active_objectsvalue->object_price;
}

$whois_proxy = array(
    'DNS_Management', 'Email_Forwarding', 'ID_Protection'
);

echo '
<div style="float: left;padding: 10px; width: 50%;">
    <form action="'.$system_url.'modules/addons/currency_manager/domainaddons/save_domainaddons.php" method="post" id="form">';

foreach ($whois_proxy as $whois_proxykey => $whois_proxyvalue) {

        echo '<div class="form-group">'.$whois_proxyvalue.' :
            <input class="form-control" size="5" type="text" name="DomainAddons_usdValue-'.$whois_proxyvalue.'" value="'.$domainaddons_values[$whois_proxyvalue]['usd'].'" />
            <label for="usdValue-'.$whois_proxyvalue.'"> usd</label><br>
            <input class="form-control" size="5" type="text" class="form-group" name="DomainAddons_euroValue-'.$whois_proxyvalue.'" value="'.$domainaddons_values[$whois_proxyvalue]['euro'].'" />
            <label for="euroValue-'.$whois_proxyvalue.'"> euro </label>
        </div><br>';
}
        echo '<input class="btn btn-primary" type="submit" value="Save Changes" class="button" name="domainaddons_submit" />
    </form>
</div>';

?>