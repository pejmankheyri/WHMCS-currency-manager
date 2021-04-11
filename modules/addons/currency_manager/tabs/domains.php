<?php

/**
 * Module for Manage Multi Currency Domains Tab
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
    ->where('object_type', '=', 'domain')
    ->get();

foreach ($active_objects as $active_objectskey => $active_objectsvalue) {
    $object_id[] = $active_objectsvalue->object_id;
    $domain_values[$active_objectsvalue->object_id][$active_objectsvalue->object_currency] = $active_objectsvalue->object_price;

    $Array_object_ids = explode('_', $active_objectsvalue->object_id);
    if (($Array_object_ids[2] == 'Renew') || ($Array_object_ids[2] == 'Transfer')) {
        $have_Renew_Transfer[] = $Array_object_ids[0]."_".$Array_object_ids[1];
    }               
}

$object_id = array_unique($object_id);

echo '<div>
<table dir="rtl" class="datatable" border="0" cellspacing="1" cellpadding="3" width="100%">
<thead>
    <tr>
    </tr>
</thead>
<tbody>
<form id="SaveDomain" method="post" action="'.$system_url.'modules/addons/currency_manager/domains/save_domain.php">';

$domainsTLD = Capsule::table('tbldomainpricing')
    ->select(['id','extension','order'])
    ->orderBy('order')
    ->get();

$domain_types = array('Register','Renew','Transfer');
$user_groups = array(
    'Main' => array('Title' => 'Main Group', 'Color' => '#d4d4d4'),
    'Reseller' => array('Title' => 'Reseller Group', 'Color' => '#eeeeee')
);

foreach ($domainsTLD as $domainsTLDkey => $domainsTLDvalue) {
    echo '
    <tr>
        <td style="text-align: right;direction: ltr;background-color: yellow;">'.$domainsTLDvalue->extension.' : TLD</td>
        <td style="text-align: right;background-color: yellow;"></td>';
    for ($i = 1; $i <= 10; $i++) {
        echo '<td style="text-align: right;background-color: yellow;"></td>';
    }
    echo '</tr>';

    foreach ($user_groups as $user_groups_key => $user_groups_value) {
        foreach ($domain_types as $domain_types_key => $domain_types_value) {

            if ($domain_types_value == 'Transfer') {
                $display = "display: none;";
                $transfer_renew_id = "hidden_transfer_rows_".$domainsTLDvalue->id.'_'.$user_groups_key;
            } elseif ($domain_types_value == 'Renew') {
                $display = "display: none;";
                $transfer_renew_id = "hidden_renew_rows_".$domainsTLDvalue->id.'_'.$user_groups_key;
            } else {
                $display = "";
                $transfer_renew_id = "rows_".$domainsTLDvalue->id.'_'.$user_groups_key;
            }

            if (in_array($domainsTLDvalue->id.'_'.$user_groups_key, $have_Renew_Transfer)) {
                $transfer_renew_checked = 'checked';
                if (($domain_types_value == 'Transfer') || ($domain_types_value == 'Renew')) {
                    $display = "table-row";
                }
            } else {
                $transfer_renew_checked = '';
            }

            echo '
            <tr style="'.$display.'" id="'.$transfer_renew_id.'">
                <td style="text-align: left;background-color: '.$user_groups_value['Color'].';">'.$user_groups_value['Title'];


            if ($domain_types_value == 'Register') {
                echo '
                    <br>
                    <label for="activate_transfer_renew_'.$domainsTLDvalue->id.'_'.$user_groups_key.'">Renew/Transfer</label>
                    <input '.$transfer_renew_checked.' type="checkbox" name="" id="activate_transfer_renew_'.$domainsTLDvalue->id.'_'.$user_groups_key.'" onclick="Transfer_Renew_selection(\''.$domainsTLDvalue->id.'_'.$user_groups_key.'\')" />
                ';
            } 

            echo '</td>';
            echo '<td style="text-align: center;background-color: '.$user_groups_value['Color'].';">'.$domain_types_value.'</td>';

            for ($i = 1; $i <= 10; $i++) {
                $object_name = $domainsTLDvalue->id.'_'.$user_groups_key.'_'.$domain_types_value.'_'.$i;

                if (in_array($object_name, $object_id)) {
                    $domain_checked = 'checked';
                    $domain_currency_visibility = 'display:block;text-align: left;';
                } else {
                    $domain_checked = '';
                    $domain_currency_visibility = 'display:none;text-align: left;';
                }

                echo '
                <td style="text-align: center;direction: ltr;background-color: '.$user_groups_value['Color'].';">
                    <label for="CheckDomain_'.$object_name.'">'.$i.' Years</label>
                    <input '.$domain_checked.' type="checkbox" name="domain_activation[]" value="'.$object_name.'" id="CheckDomain_'.$object_name.'" onclick="Domain_selection(\''.$object_name.'\')" />
                    <p id="DomainText_'.$object_name.'" style="'.$domain_currency_visibility.'">
                        <input class="form-control" size="5" type="text" name="Domain_usdValue_'.$object_name.'" value="'.$domain_values[$object_name]['usd'].'" />
                        <label for="Domain_usdValue-'.$object_name.'"> usd </label><br>
                        <input class="form-control" size="5" type="text" class="form-group" name="Domain_euroValue_'.$object_name.'" value="'.$domain_values[$object_name]['euro'].'" />
                        <label for="Domain_euroValue-'.$object_name.'"> euro </label>
                    </p>
                </td>
                ';
            }

            echo '</tr>';
        }
    }
}
echo '
            </tbody>
        </table>
    </div>            
    <input id="SaveDomainSubmitButton" class="btn btn-primary" type="submit" value="Save Changes" class="button" name="domain_submit" />
    
    <div id="SaveDomainloader" style="display: none;">
        <img src="'.$loaderFile.'">
    </div>
    <div id="SaveDomainResult"></div>

</form>
<script>
    function Domain_selection(i) {
        var checkBox = document.getElementById("CheckDomain_"+i);
        var text = document.getElementById("DomainText_"+i);
        if (checkBox.checked == true){
            text.style.display = "block";
        } else {
            text.style.display = "none";
        }
    }
    function Transfer_Renew_selection(i) {
        var checkBox = document.getElementById("activate_transfer_renew_"+i);
        var transfer_text = document.getElementById("hidden_transfer_rows_"+i);
        var renew_text = document.getElementById("hidden_renew_rows_"+i);
        if (checkBox.checked == true){
            transfer_text.style.display = "table-row";
            renew_text.style.display = "table-row";
        } else {
            transfer_text.style.display = "none";
            renew_text.style.display = "none";
        }
    }
    var j = jQuery.noConflict();
    var delayTime = 5000;
    j("#SaveDomain").submit(function(e) {
        var form = j(this);
        var url = form.attr("action");
        j.ajax({
            type: "POST",
            url: url,
            data: form.serialize(), 
            beforeSend: function(){
                j("#SaveDomainSubmitButton").hide();
                j("#SaveDomainloader").show();
            },
            success: function(data){
                j("#SaveDomainResult").empty();
                j("#SaveDomainResult").show();
                j("#SaveDomainResult").append(data);
                j("#SaveDomainResult").delay(delayTime).hide(0);
            },
            complete: function(){
                j("#SaveDomainSubmitButton").show(delayTime);
                j("#SaveDomainloader").hide();
            }
        });
        e.preventDefault(); 
    });
</script>';            

?>