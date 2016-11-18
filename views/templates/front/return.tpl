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

{extends file='page.tpl'}

{block name="page_content"}
    {if isset($error_list)}
        <div class="alert error">
            {l s='colissimo simplicite errors list:' mod='colissimo_simplicite'}
            <ul style="margin-top: 10px;">
                {foreach from=$error_list item=current_error}
                    <li>{$current_error|escape:'htmlall':'UTF-8'}</li>
                    {/foreach}
            </ul>
        </div>
        {if isset($so_url_back)}
            <a href="{$so_url_back|escape:'htmlall':'UTF-8'}" class="btn btn-primary" title="{l s='Back' mod='colissimo_simplicite'}">{l s='Back' mod='colissimo_simplicite'}</a>
        {/if}
    {else}
        <div class="waiting_colissimo">
            <img src="{$logo|escape:'htmlall':'UTF-8'}" />
            <span>{l s='You will be redirect to shop in few moment' mod='colissimo_simplicite'}</span>
            <img src="{$loader|escape:'htmlall':'UTF-8'}" /></div>
        <form name="myform" id="myformredirect" method="post" action="{$so_url_back|escape:'htmlall':'UTF-8'}">
            <input type="hidden" name="delivery_option[{$id_address|escape:'htmlall':'UTF-8'}]" id="delivery_option_{$id_so|escape:'htmlall':'UTF-8'}" value="{$id_so|escape:'htmlall':'UTF-8'},">
            <input type="hidden" name="confirmDeliveryOption" value="1">
            <input class="hidden" type="submit">
        </form>
    {/if}
{/block}

