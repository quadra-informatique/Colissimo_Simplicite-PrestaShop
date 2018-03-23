{*
* 2010-2016 La Poste SA
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to modules-prestashop@laposte.fr so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    Quadra Informatique <modules@quadra-informatique.fr>
*  @copyright 2010-2016 La Poste SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of La Poste SA
*}
<input type="hidden" value="{$wsUrl|escape:'htmlall':'UTF-8'}" id="colissimo-version"/>
<input type="hidden" id="widget-conf-message" value="{l s='Your delivery point has been registered.' mod='colissimo_simplicite'}" />
<input type="hidden" id="widget-error-message" value="{l s='Error : the web service is inaccessible, please try again later and check your configurations.' mod='colissimo_simplicite'}" />
<script type="text/javascript">
    var soInputs = new Object();
    var soCarrierId = "{$id_carrier|escape:'htmlall':'UTF-8'}";
    var initialCost_label = "{$initialCost_label|escape:'htmlall':'UTF-8'}";
    var initialCost = "{$initialCost|escape:'htmlall':'UTF-8'}";
    var taxMention = "{$taxMention|escape:'htmlall':'UTF-8'}";
    var moduleLink = "{$module_link|escape:'htmlall':'UTF-8'}";
    var colissimoModuleUrl = "{$baseUrl|escape:'htmlall':'UTF-8'}modules/colissimo_simplicite/"; 
    var colissimoModuleCss = "{$baseUrl|escape:'htmlall':'UTF-8'}modules/colissimo_simplicite/views/css/";
    var colissimoModuleJs =  "{$baseUrl|escape:'htmlall':'UTF-8'}modules/colissimo_simplicite/views/js/";
    var wsUrl = "{$wsUrl|escape:'htmlall':'UTF-8'}";
    var baseUrl = "{$baseUrl|escape:'htmlall':'UTF-8'}";
    {foreach from=$inputs item=input key=name name=myLoop}
    soInputs.{$name|escape:'htmlall':'UTF-8'} = "{$input|strip_tags|addslashes}";
    {/foreach}
</script>


<input type="hidden" id="pudoWidgetErrorCode">
<input type="hidden" id="pudoWidgetErrorCodeMessage">
<input type="hidden" id="pudoWidgetCompanyName">
<input type="hidden" id="pudoWidgetAddress1">
<input type="hidden" id="pudoWidgetAddress2">
<input type="hidden" id="pudoWidgetAddress3">
<input type="hidden" id ="pudoWidgetCity">
<input type="hidden" id="pudoWidgetZipCode">
<input type="hidden" id ="pudoWidgetCountry">
<input type="hidden" id="pudoWidgetType">
<div><div id="widget-container" class="col-xs-12"></div></div>

