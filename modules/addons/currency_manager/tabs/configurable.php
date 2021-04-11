<?php

/**
 * Module for Manage Multi Currency Configurable Options Tab
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
    ->where('object_type', '=', 'configurable')
    ->get();

foreach ($active_objects as $active_objectskey => $active_objectsvalue) {
    $object_id[] = $active_objectsvalue->object_id;
    $configurable_values[$active_objectsvalue->object_id][$active_objectsvalue->object_currency] = $active_objectsvalue->object_price;

    $Array_object_ids = explode('_', $active_objectsvalue->object_id);
    if (($Array_object_ids[1] == 'quarterly') || ($Array_object_ids[1] == 'semiannually')|| ($Array_object_ids[1] == 'annually')|| ($Array_object_ids[1] == 'biennially')|| ($Array_object_ids[1] == 'triennially')) {
        $have_different_prices[] = $Array_object_ids[0];
    }
}
$object_id = array_unique($object_id);

$configurable_periods = array(
    'monthly', 'quarterly', 'semiannually', 'annually', 'biennially'
);

echo '<div>
<table dir="rtl" class="datatable" border="0" cellspacing="1" cellpadding="3" width="100%">
<thead>
    <tr>
        <th>Configurable Options</th>';

foreach ($configurable_periods as $cp_key => $cp_value) {
    echo '<th>'.$cp_value.'</th>';
}
        
echo '</tr>
</thead>
<tbody>
<form id="SaveConfigurable" method="post" action="'.$system_url.'modules/addons/currency_manager/configurables/save_configurable.php">';

$groups = Capsule::table('tblproductconfiggroups')
    ->select('id', 'name')
    ->get();

$Configurable_Price_groups = array(
    'Setup' => array('Title' => 'Setup Fee', 'Color' => '#d4d4d4'),
    'Price' => array('Title' => 'Price', 'Color' => '#eeeeee')
);
foreach ($groups as $groupskey => $groupsvalue) {
    echo '
    <tr>
        <td style="font-weight: 700;background-color: yellow;text-align: center;">'.$groupsvalue->name.' : Group</td>';

    foreach ($configurable_periods as $cp_key => $cp_value) {
        echo '<td style="background-color: yellow;"></td>';
    }

    echo'</tr>';

    $configurable_options = Capsule::table('tblproductconfigoptions')
        ->select(['id', 'optionname'])
        ->where('gid', '=', $groupsvalue->id)
        ->get();

    foreach ($configurable_options as $configurable_optionskey => $configurable_optionsvalue) {

        echo '
        <tr>
            <td style="font-weight: 700;background-color: gray; color: #ffffff;">'.$configurable_optionsvalue->optionname.' : Option</td>';

        foreach ($configurable_periods as $cp_key => $cp_value) {
            echo '<td style="background-color: gray;"></td>';
        }

        echo'</tr>';

        $configurable_items = Capsule::table('tblproductconfigoptionssub')
            ->select(['id', 'optionname'])
            ->where('configid', '=', $configurable_optionsvalue->id)
            ->get();

        foreach ($configurable_items as $configurable_itemskey => $configurable_itemsvalue) {

            if (in_array($configurable_itemsvalue->id, $have_different_prices)) {
                $different_prices_checked = 'checked';
            } else {
                $different_prices_checked = '';
            }

            echo '
            <tr>
                <td style="text-align: center;">'.$configurable_itemsvalue->optionname.'
                <br>
                <label for="activate_different_prices_'.$groupsvalue->id.'_'.$configurable_itemsvalue->id.'">Show Other Inputs</label>
                <input '.$different_prices_checked.' type="checkbox" name="" id="activate_different_prices_'.$groupsvalue->id.'_'.$configurable_itemsvalue->id.'" onclick="Different_Prices_selection(\''.$groupsvalue->id.'_'.$configurable_itemsvalue->id.'\')" />
        
                </td>';

            foreach ($configurable_periods as $configurable_periods_key => $configurable_periods_value) {

                if (($configurable_periods_value == 'quarterly') || ($configurable_periods_value == 'semiannually') || ($configurable_periods_value == 'annually') || ($configurable_periods_value == 'biennially') || ($configurable_periods_value == 'triennially')) {
                    $display = "display: none;text-align: left; direction: ltr;";
                    $different_prices_id = "hidden_different_prices_rows_".$groupsvalue->id.'_'.$configurable_itemsvalue->id;
                    if (in_array($configurable_itemsvalue->id, $have_different_prices)) {
                        $display = "text-align: left; direction: ltr;";
                    }
                } else {
                    $display = "text-align: left; direction: ltr;";
                    $different_prices_id = "rows_".$groupsvalue->id.'_'.$configurable_itemsvalue->id;
                }

                echo '<td style="'.$display.'" name="'.$different_prices_id.'">';
                foreach ($Configurable_Price_groups as $Configurable_Price_groups_key => $Configurable_Price_groups_value) {
                    
                    $configurable_object_id = $configurable_itemsvalue->id."_".$configurable_periods_value."_".$Configurable_Price_groups_key;

                    if (in_array($configurable_object_id, $object_id)) {
                        $configurable_checked = 'checked';
                        $configurables_currency_visibility = 'display:block; text-align: left;';
                    } else {
                        $configurable_checked = '';
                        $configurables_currency_visibility = 'display:none';
                    }

                    echo '
                    <input '.$configurable_checked.' type="checkbox" name="configurable_activation[]" value="'.$configurable_object_id.'" id="CheckConfigurable-'.$configurable_object_id.'" onclick="configurable_selection(\''.$configurable_object_id.'\')" />
                    <label for="CheckConfigurable-'.$configurable_object_id.'"> '.$Configurable_Price_groups_key.' </label><br>
                    <p id="ConfigurableText-'.$configurable_object_id.'" style="'.$configurables_currency_visibility.'">
                        <input class="form-control" size="5" type="text" name="Configurable_usdValue-'.$configurable_object_id.'" value="'.$configurable_values[$configurable_object_id]['usd'].'" />
                        <label for="usdValue-'.$configurable_object_id.'"> usd</label><br>
                        <input class="form-control" size="5" type="text" class="form-group" name="Configurable_euroValue-'.$configurable_object_id.'" value="'.$configurable_values[$configurable_object_id]['euro'].'" />
                        <label for="euroValue-'.$configurable_object_id.'"> euro </label>
                    </p>';
                }
                echo '</td>';
            }
            echo'</tr>';
        }
    }
}
echo '
            </tbody>
        </table>
    </div>
    <input id="SaveConfigurableSubmitButton" class="btn btn-primary" type="submit" value="Save Changes" class="button" name="configurable_submit" />

    <div id="SaveConfigurableloader" style="display: none;">
        <img src="'.$loaderFile.'">
    </div>
    <div id="SaveConfigurableResult"></div>

</form>
<script>
    function configurable_selection(i) {
        var checkBox = document.getElementById("CheckConfigurable-"+i);
        var text = document.getElementById("ConfigurableText-"+i);
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
    j("#SaveConfigurable").submit(function(e) {
        var form = j(this);
        var url = form.attr("action");
        j.ajax({
            type: "POST",
            url: url,
            data: form.serialize(), 
            beforeSend: function(){
                j("#SaveConfigurableSubmitButton").hide();
                j("#SaveConfigurableloader").show();
            },
            success: function(data){
                j("#SaveConfigurableResult").empty();
                j("#SaveConfigurableResult").show();
                j("#SaveConfigurableResult").append(data);
                j("#SaveConfigurableResult").delay(delayTime).hide(0);
            },
            complete: function(){
                j("#SaveConfigurableSubmitButton").show(delayTime);
                j("#SaveConfigurableloader").hide();
            }
        });
        e.preventDefault(); 
    });     
</script>';


?>