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
<div class="panel">
    <fieldset style="width:400px;">
        <legend><img src="{$path_img|escape:'htmlall':'UTF-8'}" alt="" /> 
            {l s='Colissimo Simplicité' mod='colissimo_simplicite'}</legend>
        <b>{l s='Delivery mode' mod='colissimo_simplicite'} : </b>
        {if $is_home}
            <span>{$delivery_mode[$delivery_infos->delivery_mode]|escape:'html':'UTF-8'}</span><br /><br />
            <span><b>{l s='Customer' mod='colissimo_simplicite'} : </b>
                {$address_delivery->firstname|escape:'html':'UTF-8'} {$address_delivery->lastname|escape:'html':'UTF-8'}</span><br />
            {if $delivery_infos->cecompanyname}<b>
                    {l s='Company' mod='colissimo_simplicite'} : </b>{$delivery_infos->cecompanyname|escape:'html':'UTF-8'}<br/>

            {/if}
            {if $delivery_infos->ceemail}<b>
                    {l s='E-mail address' mod='colissimo_simplicite'} : </b>{$delivery_infos->ceemail|escape:'html':'UTF-8'}<br/>
                {/if}
                {if $delivery_infos->cephonenumber}<b>
                    {l s='Phone' mod='colissimo_simplicite'} : </b>{$delivery_infos->cephonenumber|escape:'html':'UTF-8'}<br/><br/>
                {/if}
            <b>{l s='Customer address' mod='colissimo_simplicite'} : </b><br/>
            {$address_delivery->address1|escape:'html':'UTF-8'}<br />
            {$address_delivery->address2|escape:'html':'UTF-8'}<br />
            {$address_delivery->postcode|escape:'html':'UTF-8'}<br />
            {$address_delivery->city|escape:'html':'UTF-8'}<br />
            {$address_delivery->country|escape:'html':'UTF-8'}<br />
            {if $address_delivery->other}<hr><b>
                    {l s='Other' mod='colissimo_simplicite'} : </b>{$address_delivery->other|escape:'html':'UTF-8'}<br /><br />
                {/if}
                {if $delivery_infos->cedoorcode1}<b>
                    {l s='Door code' mod='colissimo_simplicite'} 1 : </b>{$delivery_infos->cedoorcode1|escape:'html':'UTF-8'}<br/>
                {/if}
                {if $delivery_infos->cedoorcode2}<b>
                    {l s='Door code' mod='colissimo_simplicite'} 2 : </b>{$delivery_infos->cedoorcode2|escape:'html':'UTF-8'}<br/>
                {/if}
                {if $delivery_infos->cedeliveryinformation}<b>{l s='Delivery information' mod='colissimo_simplicite'} : </b>
                {$delivery_infos->cedeliveryinformation|escape:'htmlall':'UTF-8'}<br/><br/>
            {/if}
        {else}
            <span>{$delivery_mode[$delivery_infos->delivery_mode]|escape:'html':'UTF-8'}</span><br /><br />
            {if $delivery_infos->prid}<b>
                    {l s='Pick up point ID' mod='colissimo_simplicite'} : </b>{$delivery_infos->prid|escape:'html':'UTF-8'}<br/>
                {/if}
                {if $delivery_infos->prname}<b>
                    {l s='Pick up point' mod='colissimo_simplicite'} : </b>{$delivery_infos->prname|escape:'html':'UTF-8'}<br/>
                {/if}
            <b>{l s='Pick up point address' mod='colissimo_simplicite'} : </b><br/>
            {if $delivery_infos->pradress1}
                {$delivery_infos->pradress1|escape:'html':'UTF-8'}<br/>
            {/if}
            {if $delivery_infos->pradress2}
                {$delivery_infos->pradress2|escape:'html':'UTF-8'}<br/>
            {/if}
            {if $delivery_infos->pradress3}
                {$delivery_infos->pradress3|escape:'html':'UTF-8'}<br/>
            {/if}
            {if $delivery_infos->pradress4}
                {$delivery_infos->pradress4|escape:'html':'UTF-8'}<br/>
            {/if}
            {$delivery_infos->przipcode|escape:'html':'UTF-8'}<br/>
            {$delivery_infos->prtown|escape:'html':'UTF-8'}<br/>
            {$name_country|escape:'htmlall':'UTF-8'}<br/>
            {if $delivery_infos->ceemail}<b>
                    {l s='Email' mod='colissimo_simplicite'} : </b>{$delivery_infos->ceemail|escape:'html':'UTF-8'}<br/>
                {/if}
                {if $delivery_infos->cephonenumber}<b>
                    {l s='Phone' mod='colissimo_simplicite'} : </b>{$delivery_infos->cephonenumber|escape:'html':'UTF-8'}<br/>
                {/if}

        {/if}
    </fieldset>
</div>