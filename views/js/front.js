/*
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
 *  @author PrestaShop SA <contact@prestashop.com>
 *  @copyright  2007-2016 PrestaShop SA
 *  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

$(document).ready(function ()
{
    $('#soLink').fancybox({
        'width': 590,
        'height': 810,
        'autoScale': true,
        'centerOnScroll': true,
        'autoDimensions': false,
        'transitionIn': 'none',
        'transitionOut': 'none',
        'hideOnOverlayClick': false,
        'hideOnContentClick': false,
        'showCloseButton': true,
        'showIframeLoading': true,
        'enableEscapeButton': true,
        'type': 'iframe',
        onStart: function () {
            $('#soLink').attr('href', link_socolissimo + serialiseInput(soInputs));
        },
        onClosed: function () {
            $.ajax({
                type: 'GET',
                url: baseDir + '/modules/socolissimo/ajax.php',
                async: false,
                cache: false,
                dataType: "json",
                data: "token=" + soToken,
                success: function (jsonData) {
                    if (jsonData && jsonData.answer && typeof jsonData.answer != undefined && !opc) {
                        if (jsonData.answer)
                            $('#form').submit();
                        else if (jsonData.msg.length)
                            alert(jsonData.msg);
                    }
                },
                error: function (XMLHttpRequest, textStatus, errorThrown) {
                    alert('TECHNICAL ERROR\nDetails:\nError thrown: ' + XMLHttpRequest + '\n' + 'Text status: ' + textStatus);
                }
            });
        }
    });


    $('.delivery-option input:radio').each(function ()
    {
        if ($(this).val() == soCarrierId + ',') {
            $(this).parent().parent().parent().find('.carrier-price').html(initialCost_label + '<br/>' + initialCost + taxMention);
        }
    });

    $('.delivery-option input:radio').change(function () {
        selectedCarrier();
    });
    selectedCarrier();

});
function redirect()
{
    $('#soLink').attr('href', link_socolissimo + serialiseInput(soInputs));
    $("#soLink").trigger("click");
    return false;
}
function redirect_mobile()
{
    $('#button_socolissimo').attr('href', link_socolissimo_mobile + serialiseInput(soInputs));
    $("#button_socolissimo").trigger("click");
    return false;
}

function selectedCarrier() {
    if ($('.delivery-option input:radio:checked').val() == soCarrierId + ',')
    {
        $('.delivery-option input:radio:checked').parent().parent().parent().find('.carrier-extra-content').show();
        $('#button_socolissimo').show();
    }
    else {
        $('#button_socolissimo').hide();
    }
}
function serialiseInput(inputs)
{
    if (!rewriteActive)
        var str = '&first_call=1&';
    else
        var str = '?first_call=1&';
    for (var cle in inputs)
        str += cle + '=' + inputs[cle] + '&';
    return (str + 'gift=' + $('#gift').attr('checked') + '&gift_message=' + $('#gift_message').attr('value'));
}