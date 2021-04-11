<?php

/**
 * Module for Manage Multi Currency Configuration Tab
 * 
 * PHP version 5.6.x | 7.x | 8.x
 * 
 * @category  Addons
 * @package   Whmcs
 * @author    Pejman Kheyri <pejmankheyri@gmail.com>
 * @copyright 2021 All rights reserved.
 */

echo '
<div style="float: left;padding: 10px; width: 50%;">
    <h2>Currency Management</h2>
    <form action="'.$system_url.'modules/addons/currency_manager/save_configurations.php" method="post" id="form">
        <div class="form-group">
            <label for="usd_value">USD ('.$usd_symbol.') : </label>
            <input value="'.$usd_value.'" type="text" name="usd_value" id="usd_value" class="form-control" />
        </div><br>
        <div class="form-group">
            <label for="euro_value">Euro ('.$euro_symbol.') : </label>
            <input value="'.$euro_value.'" type="text" name="euro_value" class="form-control" id="euro_value" />
        </div><br>
        <div class="form-group">
            <label for="pound_value">Pound ('.$pound_symbol.') : </label>
            <input value="'.$pound_value.'" type="text" name="pound_value" class="form-control" id="pound_value" />
        </div><br>
        <input class="btn btn-primary" type="submit" value="Save Changes" class="button" />
    </form>
</div>
<div style="float: right; text-align: right;padding: 10px; width: 50%;">
    <h2>Update Prices</h2>
    <div style="float: right; text-align: right;padding: 10px; width: 50%;">
        <form id="ProductForm" action="'.$system_url.'modules/addons/currency_manager/products/update_product.php" method="POST">
            <div class="form-group">
                <input class="btn btn-warning" type="submit" value="Update Products" id="ProductSubmitButton">
            </div>
            <div id="Productloader" style="display: none;">
                <img src="'.$loaderFile.'">
            </div>
            <div id="ProductResult"></div>
        </form>
        <form id="DomainForm" action="'.$system_url.'modules/addons/currency_manager/domains/update_domain.php" method="POST">
            <div class="form-group">
                <input class="btn btn-warning" type="submit" value="Update Domains" id="DomainSubmitButton">
            </div>
            <div id="Domainloader" style="display: none;">
                <img src="'.$loaderFile.'">
            </div>
            <div id="DomainResult"></div>
        </form>
    </div>
    <div style="float: right; text-align: left;padding: 10px; width: 50%;">
        <form id="AddonForm" action="'.$system_url.'modules/addons/currency_manager/addons/update_addon.php" method="POST">
            <div class="form-group">
                <input class="btn btn-warning" type="submit" value="Update Addons" id="AddonSubmitButton">
            </div>
            <div id="Addonloader" style="display: none;">
                <img src="'.$loaderFile.'">
            </div>
            <div id="AddonResult"></div>
        </form>
        <form id="ConfigurableForm" action="'.$system_url.'modules/addons/currency_manager/configurables/update_configurable.php" method="POST">
            <div class="form-group">
                <input class="btn btn-warning" type="submit" value="Update Configurable Options" id="ConfigurableSubmitButton">
            </div>
            <div id="Configurableloader" style="display: none;">
                <img src="'.$loaderFile.'">
            </div>
            <div id="ConfigurableResult"></div>
        </form>
    </div>
    <div style="float: right; text-align: left;padding: 10px; width: 100%;">
        <form id="DomainAddonsForm" action="'.$system_url.'modules/addons/currency_manager/domainaddons/update_domainaddons.php" method="POST">
            <div class="form-group">
                <input class="btn btn-warning" type="submit" value="Update Domain Addons" id="DomainAddonsSubmitButton">
            </div>
            <div id="DomainAddonsloader" style="display: none;">
                <img src="'.$loaderFile.'" >
            </div>
            <div id="DomainAddonsResult"></div>
        </form>        
    </div>
</div>
<script type="text/javascript">
    var j = jQuery.noConflict();
    var delayTime = 5000;
    j("#AddonForm").submit(function(e) {
        var form = j(this);
        var url = form.attr("action");
        j.ajax({
            type: "POST",
            url: url,
            data: form.serialize(), 
            beforeSend: function(){
                j("#AddonSubmitButton").hide();
                j("#Addonloader").show();
            },
            success: function(data){
                j("#AddonResult").empty();
                j("#AddonResult").show();
                j("#AddonResult").append(data);
                j("#AddonResult").delay(delayTime).hide(0);
            },
            complete: function(){
                j("#AddonSubmitButton").show(delayTime);
                j("#Addonloader").hide();
            }
        });
        e.preventDefault(); 
    });
    j("#ProductForm").submit(function(e) {
        var form = j(this);
        var url = form.attr("action");
        j.ajax({
            type: "POST",
            url: url,
            data: form.serialize(), 
            beforeSend: function(){
                j("#ProductSubmitButton").hide();
                j("#Productloader").show();
            },
            success: function(data){
                j("#ProductResult").empty();
                j("#ProductResult").show();
                j("#ProductResult").append(data);
                j("#ProductResult").delay(delayTime).hide(0);
            },
            complete: function(){
                j("#ProductSubmitButton").show(delayTime);
                j("#Productloader").hide();
            }
        });
        e.preventDefault(); 
    });
    j("#ConfigurableForm").submit(function(e) {
        var form = j(this);
        var url = form.attr("action");
        j.ajax({
            type: "POST",
            url: url,
            data: form.serialize(), 
            beforeSend: function(){
                j("#ConfigurableSubmitButton").hide();
                j("#Configurableloader").show();
            },
            success: function(data){
                j("#ConfigurableResult").empty();
                j("#ConfigurableResult").show();
                j("#ConfigurableResult").append(data);
                j("#ConfigurableResult").delay(delayTime).hide(0);
            },
            complete: function(){
                j("#ConfigurableSubmitButton").show(delayTime);
                j("#Configurableloader").hide();
            }
        });
        e.preventDefault(); 
    });
    j("#DomainForm").submit(function(e) {
        var form = j(this);
        var url = form.attr("action");
        j.ajax({
            type: "POST",
            url: url,
            data: form.serialize(), 
            beforeSend: function(){
                j("#DomainSubmitButton").hide();
                j("#Domainloader").show();
            },
            success: function(data){
                j("#DomainResult").empty();
                j("#DomainResult").show();
                j("#DomainResult").append(data);
                j("#DomainResult").delay(delayTime).hide(0);
            },
            complete: function(){
                j("#DomainSubmitButton").show(delayTime);
                j("#Domainloader").hide();
            }
        });
        e.preventDefault(); 
    });
    j("#DomainAddonsForm").submit(function(e) {
        var form = j(this);
        var url = form.attr("action");
        j.ajax({
            type: "POST",
            url: url,
            data: form.serialize(), 
            beforeSend: function(){
                j("#DomainAddonsSubmitButton").hide();
                j("#DomainAddonsloader").show();
            },
            success: function(data){
                j("#DomainAddonsResult").empty();
                j("#DomainAddonsResult").show();
                j("#DomainAddonsResult").append(data);
                j("#DomainAddonsResult").delay(delayTime).hide(0);
            },
            complete: function(){
                j("#DomainAddonsSubmitButton").show(delayTime);
                j("#DomainAddonsloader").hide();
            }
        });
        e.preventDefault(); 
    });    
</script>';

?>