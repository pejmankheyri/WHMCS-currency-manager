<?php

/**
 * Module for Manage Multi Currency Addons Tab
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
    ->where('object_type', '=', 'addon')
    ->get();

foreach ($active_objects as $active_objectskey => $active_objectsvalue) {
    $object_id[] = $active_objectsvalue->object_id;
    $addon_values[$active_objectsvalue->object_id][$active_objectsvalue->object_currency] = $active_objectsvalue->object_price;

    $Array_object_ids = explode('_', $active_objectsvalue->object_id);
    if (($Array_object_ids[1] == 'quarterly') || ($Array_object_ids[1] == 'semiannually')|| ($Array_object_ids[1] == 'annually')|| ($Array_object_ids[1] == 'biennially')|| ($Array_object_ids[1] == 'triennially')) {
        $have_different_prices[] = $Array_object_ids[0];
    }
}
$object_id = array_unique($object_id);

$addon_periods = array(
    'monthly', 'quarterly', 'semiannually', 'annually'
);

echo '<div>
<table dir="rtl" class="datatable" border="0" cellspacing="1" cellpadding="3" width="100%">
<thead>
    <tr>
        <th>Addon Name</th>';

foreach ($addon_periods as $ap_key => $ap_value) {
    echo '<th>'.$ap_value.'</th>';
}
        
echo '</tr>
</thead>
<tbody>
<form id="SaveAddon" method="post" action="'.$system_url.'modules/addons/currency_manager/addons/save_addon.php">';

$Addon_Price_groups = array(
    'Setup' => array('Title' => 'Setup Fee', 'Color' => '#d4d4d4'),
    'Price' => array('Title' => 'Price', 'Color' => '#eeeeee')
);

$addons = Capsule::table('tbladdons')
    ->select(['id', 'name'])
    ->get();

foreach ($addons as $addonkey => $addonvalue) {

    if (in_array($addonvalue->id, $have_different_prices)) {
        $different_prices_checked = 'checked';
    } else {
        $different_prices_checked = '';
    }

    echo '
    <tr>
        <td style="text-align: center;">'.$addonvalue->name.'
        <br>
        <label for="activate_different_prices_'.$addonvalue->id.'">Show Other Inputs</label>
        <input '.$different_prices_checked.' type="checkbox" name="" id="activate_different_prices_'.$addonvalue->id.'" onclick="Different_Prices_selection(\''.$addonvalue->id.'\')" />

        </td>';

    foreach ($addon_periods as $addon_periods_key => $addon_periods_value) {

        if (($addon_periods_value == 'quarterly') || ($addon_periods_value == 'semiannually') || ($addon_periods_value == 'annually') || ($addon_periods_value == 'biennially') || ($addon_periods_value == 'triennially')) {
            $display = "display: none;text-align: left; direction: ltr;";
            $different_prices_id = "hidden_different_prices_rows_".$addonvalue->id;
            if (in_array($addonvalue->id, $have_different_prices)) {
                $display = "text-align: left; direction: ltr;";
            }
        } else {
            $display = "text-align: left; direction: ltr;";
            $different_prices_id = "rows_".$addonvalue->id;
        }

        echo '<td style="'.$display.'" name="'.$different_prices_id.'">';
        foreach ($Addon_Price_groups as $Addon_Price_groups_key => $Addon_Price_groups_value) {
            
            $addon_object_id = $addonvalue->id."_".$addon_periods_value."_".$Addon_Price_groups_key;

            if (in_array($addon_object_id, $object_id)) {
                $addon_checked = 'checked';
                $addons_currency_visibility = 'display:block; text-align: left;';
            } else {
                $addon_checked = '';
                $addons_currency_visibility = 'display:none';
            }

            echo '
            <input '.$addon_checked.' type="checkbox" name="addon_activation[]" value="'.$addon_object_id.'" id="CheckAddon-'.$addon_object_id.'" onclick="addon_selection(\''.$addon_object_id.'\')" />
            <label for="CheckAddon-'.$addon_object_id.'"> '.$Addon_Price_groups_key.' </label><br>
            <p id="AddonText-'.$addon_object_id.'" style="'.$addons_currency_visibility.'">
                <input class="form-control" size="5" type="text" name="Addon_usdValue-'.$addon_object_id.'" value="'.$addon_values[$addon_object_id]['usd'].'" />
                <label for="usdValue-'.$addon_object_id.'"> usd</label><br>
                <input class="form-control" size="5" type="text" class="form-group" name="Addon_euroValue-'.$addon_object_id.'" value="'.$addon_values[$addon_object_id]['euro'].'" />
                <label for="euroValue-'.$addon_object_id.'"> euro </label>
            </p>';
        }
        echo '</td>';
    }
    echo'</tr>';
}
echo '
            </tbody>
        </table>
    </div>
    <input id="SaveAddonSubmitButton" class="btn btn-primary" type="submit" value="Save Changes" class="button" name="addon_submit" />

    <div id="SaveAddonloader" style="display: none;">
        <img src="'.$loaderFile.'">
    </div>
    <div id="SaveAddonResult"></div>

</form>
<script>
    function addon_selection(i) {
        var checkBox = document.getElementById("CheckAddon-"+i);
        var text = document.getElementById("AddonText-"+i);
        if (checkBox.checked == true){
            text.style.display = "block";
        } else {
            text.style.display = "none";
        }
    }
    function Different_Prices_selection(i) {
        var checkBox = document.getElementById("activate_different_prices_"+i);
        var different_price_text = document.getElementsByName("hidden_different_prices_rows_"+i);

        if (checkBox.checked == true){
            for (var j = 0, max = different_price_text.length; j < max; j++) {
                different_price_text[j].style.display = "";
            }
        } else {
            for (var k = 0, max = different_price_text.length; k < max; k++) {
                different_price_text[k].style.display = "none";
            }
        }
    }
    var j = jQuery.noConflict();
    var delayTime = 5000;
    j("#SaveAddon").submit(function(e) {
        var form = j(this);
        var url = form.attr("action");
        j.ajax({
            type: "POST",
            url: url,
            data: form.serialize(), 
            beforeSend: function(){
                j("#SaveAddonSubmitButton").hide();
                j("#SaveAddonloader").show();
            },
            success: function(data){
                j("#SaveAddonResult").empty();
                j("#SaveAddonResult").show();
                j("#SaveAddonResult").append(data);
                j("#SaveAddonResult").delay(delayTime).hide(0);
            },
            complete: function(){
                j("#SaveAddonSubmitButton").show(delayTime);
                j("#SaveAddonloader").hide();
            }
        });
        e.preventDefault(); 
    });    
</script>';

?>