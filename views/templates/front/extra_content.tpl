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
<a href="#" style="display:none" class="fancybox fancybox.iframe" id="soLink"></a>
<a class="btn btn-primary" id="button_socolissimo" onclick="{if $on_mobile_device}redirect_mobile();{else}redirect();{/if}" >{$select_label|escape:'htmlall':'UTF-8'}
</a>

<script type="text/javascript">
    var link_socolissimo = "{$link_socolissimo|escape:'htmlall':'UTF-8'}";
    var link_socolissimo_mobile = "{$link_socolissimo_mobile|escape:'htmlall':'UTF-8'}";
    var soInputs = new Object();
    var soCarrierId = "{$id_carrier|escape:'htmlall':'UTF-8'}";
    var soToken = "{$token|escape:'htmlall':'UTF-8'}";
    var initialCost_label = "{$initialCost_label|escape:'htmlall':'UTF-8'}";
    var initialCost = "{$initialCost|escape:'htmlall':'UTF-8'}";
    var taxMention = "{$taxMention|escape:'htmlall':'UTF-8'}";
    var rewriteActive = '{$rewrite_active|escape:'htmlall':'UTF-8'}';
    {foreach from = $inputs item = input key = name name = myLoop}
    soInputs.{$name|escape:'htmlall':'UTF-8'} = "{$input|strip_tags|addslashes}";
    {/foreach}
</script>
