/*
 * 2007-2018 PrestaShop
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
 *  @author PrestaShop SA <contact@prestashop.com>
 *  @copyright  2007-2018 PrestaShop SA
 *  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

$(document).ready(function ()
{
    $('button[name=confirmDeliveryOption]').click(function(e){
        if ($('.delivery-option input:radio:checked').val() == soCarrierId + ',') {
            var have_selected_point = parseInt($('#have_selected_point').val());
            if (!have_selected_point) {
                alert(msg_order_carrier_colissimo);
                e.preventDefault();
            }
        }
    });
    /* iframe génération */
    /* hidding iframe if carrier Colissimo Simplicité is not selected */

    /*if ($('#widget-container').length) {
     
     $('#widget-container').hide();
     jQuery("#colissimo-container").html(data);
     }*/
    /*var id_hook = $('#colissimo-version').parent().attr('id');
     alert(id_hook);*/

    $('.delivery-option input:radio').change(function () {
        if (!$('#widget-container').html()) {
            //$('#footer').append('<div id="widget-container" class="col-xs-12"></div>');
            generateMap();
        }
    });
    if(typeof soCarrierId !== 'undefined') {
        if ($('.delivery-option input:radio:checked').val() == soCarrierId + ',') {
            if (!$('#widget-container').html()) {
                generateMap();
            }
        /* if (!$('#widget-container').length) {
         $('#footer').append('<div id="widget-container" class="col-xs-12"></div>');
         generateMap();
         }
         $('#' + id_hook).append($('#widget-container'));
         $('#widget-container').show();
         } else {
         //$('.delivery-option input:radio').change(function () {
         $('#footer').append($('#widget-container'));
        //});*/
        }
    }
});
var generateMap = function () {
    $(function () {
        $('#widget-container').frameColiposteOpen({
            "ceLang": soInputs.ceLang,
            "callBackFrame": "callBackFrame",
            "ceCountryList": "FR,BE,DE,NL,LU,ES,GB,PT,AT,EE,LV,LT",
            "dyPreparationTime": soInputs.dyPreparationTime,
            "ceAddress": soInputs.ceAddress,
            "ceZipCode": soInputs.ceZipCode,
            "ceTown": soInputs.ceTown,
            "ceCountry": soInputs.cePays,
            "token": soInputs.token
        });

    });
}

function callBackFrame(point) {
    saveDeliveryPoint(point, moduleLink);
}