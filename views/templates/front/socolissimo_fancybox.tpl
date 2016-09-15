{*
* 2007-2016 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com> Quadra Informatique <modules@quadra-informatique.fr>
*  @copyright 2007-2016 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

<style type="text/css">
	.soBackward_compat_tab {literal}{ text-align: center; }{/literal}
	.soBackward_compat_tab a {literal}{ margin: 10px; }{/literal}
</style>

<a href="#" style="display:none" class="fancybox fancybox.iframe" id="soLink"></a>
{if isset($opc) && $opc}
	<script type="text/javascript">
		var opc = true;
	</script>
{else}
	<script type="text/javascript">
		var opc = false;
	</script>
{/if}
{if isset($already_select_delivery) && $already_select_delivery}
	<script type="text/javascript">
		var already_select_delivery = true;
	</script>
{else}
	<script type="text/javascript">
		var already_select_delivery = false;
	</script>
{/if}

<script type="text/javascript">
	var link_socolissimo = "{$link_socolissimo|escape:'UTF-8'}";
	var soInputs = new Object();
	var soBwdCompat = "{$SOBWD_C|escape:'htmlall'}";
	var soCarrierId = "{$id_carrier|escape:'htmlall'}";
	var soSellerId = "{$id_carrier_seller|escape:'htmlall'}";
	var soToken = "{$token|escape:'htmlall'}";
	var initialCost_label = "{$initialCost_label|escape:'htmlall'}";
	var initialCost = "{$initialCost|escape:'htmlall'}";
	var taxMention = "{$taxMention|escape:'htmlall'}";
	var baseDir = '{$content_dir|escape:'htmlall'}';
	var rewriteActive = '{$rewrite_active|escape:'htmlall'}';
	{foreach from=$inputs item=input key=name name=myLoop}
        soInputs.{$name} = "{$input|strip_tags|addslashes}";
	{/foreach}
</script>
