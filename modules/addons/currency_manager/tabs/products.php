<?php

/**
 * Module for Manage Multi Currency Products Tab
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
    ->where('object_type', '=', 'product')
    ->get();

foreach ($active_objects as $active_objectskey => $active_objectsvalue) {
    $object_id[] = $active_objectsvalue->object_id;
    $product_values[$active_objectsvalue->object_id][$active_objectsvalue->object_currency] = $active_objectsvalue->object_price;

    $Array_object_ids = explode('_', $active_objectsvalue->object_id);
    if (($Array_object_ids[1] == 'quarterly') || ($Array_object_ids[1] == 'semiannually')|| ($Array_object_ids[1] == 'annually')|| ($Array_object_ids[1] == 'biennially')|| ($Array_object_ids[1] == 'triennially')) {
        $have_different_prices[] = $Array_object_ids[0];
    }
}
$object_id = array_unique($object_id);

$product_periods = array(
    'monthly', 'quarterly', 'semiannually', 'annually', 'biennially'
);

echo '<div>
<table dir="rtl" class="datatable" border="0" cellspacing="1" cellpadding="3" width="100%">
<thead>
    <tr>
        <th>Product Name</th>';

foreach ($product_periods as $pp_key => $pp_value) {
    echo '<th>'.$pp_value.'</th>';
}
        
echo '</tr>
</thead>
<tbody>
<form id="SaveProduct" method="post" action="'.$system_url.'modules/addons/currency_manager/products/save_product.php">';

$groups = Capsule::table('tblproductgroups')
    ->select('id', 'name')
    ->get();

$Product_Price_groups = array(
    'Setup' => array('Title' => 'Setup Fee', 'Color' => '#d4d4d4'),
    'Price' => array('Title' => 'Price', 'Color' => '#eeeeee')
);
foreach ($groups as $groupskey => $groupsvalue) {
    echo '
    <tr>
        <td style="font-weight: 700;background-color: yellow;">'.$groupsvalue->name.' : Group</td>';

    foreach ($product_periods as $pp_key => $pp_value) {
        echo '<td style="background-color: yellow;"></td>';
    }

    echo'</tr>';

    $products = Capsule::table('tblproducts')
        ->select(['id as pid', 'type as ptype', 'name as pname'])
        ->where('gid', '=', $groupsvalue->id)
        ->get();

    foreach ($products as $productskey => $productvalue) {

        if (in_array($productvalue->pid, $have_different_prices)) {
            $different_prices_checked = 'checked';
        } else {
            $different_prices_checked = '';
        }

        echo '
        <tr>
            <td style="text-align: center;">'.$productvalue->pname.' - '.$productvalue->ptype.'
            <br>
            <label for="activate_different_prices_'.$groupsvalue->id.'_'.$productvalue->pid.'">Show Other Inputs</label>
            <input '.$different_prices_checked.' type="checkbox" name="activate_different_prices[]" value="'.$productvalue->pid.'" id="activate_different_prices_'.$groupsvalue->id.'_'.$productvalue->pid.'" onclick="Different_Prices_selection(\''.$groupsvalue->id.'_'.$productvalue->pid.'\')" />
    
            </td>';

        foreach ($product_periods as $product_periods_key => $product_periods_value) {

            if (($product_periods_value == 'quarterly') || ($product_periods_value == 'semiannually') || ($product_periods_value == 'annually') || ($product_periods_value == 'biennially') || ($product_periods_value == 'triennially')) {
                $display = "display: none;text-align: left; direction: ltr;";
                $different_prices_id = "hidden_different_prices_rows_".$groupsvalue->id.'_'.$productvalue->pid;
                if (in_array($productvalue->pid, $have_different_prices)) {
                    $display = "text-align: left; direction: ltr;";
                }
            } else {
                $display = "text-align: left; direction: ltr;";
                $different_prices_id = "rows_".$groupsvalue->id.'_'.$productvalue->pid;
            }

            echo '<td style="'.$display.'" name="'.$different_prices_id.'">';
            foreach ($Product_Price_groups as $Product_Price_groups_key => $Product_Price_groups_value) {
                
                $product_object_id = $productvalue->pid."_".$product_periods_value."_".$Product_Price_groups_key;

                if (in_array($product_object_id, $object_id)) {
                    $product_checked = 'checked';
                    $products_currency_visibility = 'display:block; text-align: left;';
                } else {
                    $product_checked = '';
                    $products_currency_visibility = 'display:none';
                }

                echo '
                <input '.$product_checked.' type="checkbox" name="product_activation[]" value="'.$product_object_id.'" id="CheckProduct-'.$product_object_id.'" onclick="product_selection(\''.$product_object_id.'\')" />
                <label for="CheckProduct-'.$product_object_id.'"> '.$Product_Price_groups_key.' </label><br>
                <p id="ProductText-'.$product_object_id.'" style="'.$products_currency_visibility.'">
                    <input class="form-control" size="5" type="text" name="Product_usdValue-'.$product_object_id.'" value="'.$product_values[$product_object_id]['usd'].'" />
                    <label for="usdValue-'.$product_object_id.'"> usd</label><br>
                    <input class="form-control" size="5" type="text" class="form-group" name="Product_euroValue-'.$product_object_id.'" value="'.$product_values[$product_object_id]['euro'].'" />
                    <label for="euroValue-'.$product_object_id.'"> euro </label>
                </p>';
            }
            echo '</td>';
        }
        echo'</tr>';
    }
}
echo '
            </tbody>
        </table>
    </div>
    <input id="SaveProductSubmitButton" class="btn btn-primary" type="submit" value="Save Changes" class="button" name="product_submit" />

    <div id="SaveProductloader" style="display: none;">
        <img src="'.$loaderFile.'">
    </div>
    <div id="SaveProductResult"></div>

</form>
<script>
    function product_selection(i) {
        var checkBox = document.getElementById("CheckProduct-"+i);
        var text = document.getElementById("ProductText-"+i);
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
    j("#SaveProduct").submit(function(e) {
        var form = j(this);
        var url = form.attr("action");
        j.ajax({
            type: "POST",
            url: url,
            data: form.serialize(), 
            beforeSend: function(){
                j("#SaveProductSubmitButton").hide();
                j("#SaveProductloader").show();
            },
            success: function(data){
                j("#SaveProductResult").empty();
                j("#SaveProductResult").show();
                j("#SaveProductResult").append(data);
                j("#SaveProductResult").delay(delayTime).hide(0);
            },
            complete: function(){
                j("#SaveProductSubmitButton").show(delayTime);
                j("#SaveProductloader").hide();
            }
        });
        e.preventDefault(); 
    });
</script>';

?>