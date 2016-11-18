<?php
/**
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
 */

require_once _PS_MODULE_DIR_.'colissimo_simplicite/classes/SCFields.php';
require_once _PS_MODULE_DIR_.'colissimo_simplicite/models/ColissimoDeliveryInfo.php';

class Colissimo_SimpliciteValidationModuleFrontController extends ModuleFrontController
{

    public $ssl = true;

    /**
     * @see FrontController::initContent()
     */
    public function initContent()
    {
        parent::initContent();
        $errors_list = false;
        /* Init the Context (inherit Socolissimo and handle error) */
        if (!Tools::getValue('DELIVERYMODE')) {
            /* api 4.0 mobile */
            $so = new SCFields(Tools::getValue('deliveryMode'));
        } else {
            /* api 4.0 */
            $so = new SCFields(Tools::getValue('DELIVERYMODE'));
        }

        $redirect = __PS_BASE_URI__.'index.php?controller=order';
        $so->context->smarty->assign('so_url_back', $redirect);
        $return = array();

        /* If error code not defined or empty / null */
        $errors_codes = ($tab = Tools::getValue('ERRORCODE')) ? explode(' ', trim($tab)) : array();
        /* If no required error code, start to get the POST data */
        if (!$so->checkErrors($errors_codes, SCError::REQUIRED)) {
            foreach ($_GET as $key => $val) {
                if ($so->isAvailableFields($key)) {
                    /* only way to know if api is 3.0 to get encode for accentued chars in key calculation */
                    if (!Tools::getValue('CHARSET')) {
                        $return[Tools::strtoupper($key)] = utf8_encode(Tools::stripslashes($val));
                    } else {
                        $return[Tools::strtoupper($key)] = Tools::stripslashes($val);
                    }
                }
            }
            /* GET parameter, the only one */
            $return['TRRETURNURLKO'] = Tools::getValue('trReturnUrlKo'); /* api 4.0 mobile */
            if (!$return['TRRETURNURLKO']) {
                $return['TRRETURNURLKO'] = Tools::getValue('TRRETURNURLKO'); /* api 4.0 */
            } else {
                /* Treating parameters for api 4.0 mobile */
                if (empty($return['TRINTER'])) {
                    /* 0 by default */
                    $return['TRINTER'] = 0;
                }
                if (empty($return['CELANG'])) {
                    /* fr_FR by default */
                    $return['CELANG'] = 'fr_FR';
                }
                if (!empty($return['PRPAYS'])) {
                    unset($return['PRPAYS']);
                }
                if (!empty($return['CODERESEAU'])) {
                    unset($return['CODERESEAU']);
                }
            }
            foreach ($so->getFields(SCFields::REQUIRED) as $field) {
                if (!isset($return[$field])) {
                    $errors_list[] = $so->l('This key is required for Socolissimo:').$field;
                }
            }
        } else {
            foreach ($errors_codes as $code) {
                $errors_list[] = $so->l('Error code:').' '.$so->getError($code);
            }
        }

        if (empty($errors_list)) {
            if ($so->isCorrectSignKey($return['SIGNATURE'], $return) && $so->context->cart->id && $this->saveOrderShippingDetails($so->context->cart->id, (int)$return['TRCLIENTNUMBER'], $return, $so)) {
                $trparamplus = explode('|', $return['TRPARAMPLUS']);

                if (count($trparamplus) > 1) {
                    $so->context->cart->id_carrier = (int)$trparamplus[0];
                    if ($trparamplus[1] == 'checked' || $trparamplus[1] == 1 || $trparamplus[1] == 'true') {
                        /* value can be "undefined" or "not checked" */
                        $so->context->cart->gift = 1;
                    } else {
                        $so->context->cart->gift = 0;
                    }
                } elseif (count($trparamplus) == 1) {
                    $so->context->cart->id_carrier = (int)$trparamplus[0];
                }

                if ((int)$so->context->cart->gift && Validate::isMessage($trparamplus[2])) {
                    $so->context->cart->gift_message = strip_tags($trparamplus[2]);
                }

                if (!$so->context->cart->update()) {
                    $errors_list[] = $so->l('Cart cannot be updated. Please try again your selection');
                } else {
                    $so->context->smarty->assign('is_valid', 1);
                    $so->context->smarty->assign('id_address', $so->context->cart->id_address_delivery);
                    $so->context->smarty->assign('id_so', $so->context->cart->id_carrier);
                    $so->context->smarty->assign('logo', Tools::getHttpHost(true).__PS_BASE_URI__.'modules/colissimo_simplicite/logo.gif');
                    $so->context->smarty->assign('loader', Tools::getHttpHost(true).__PS_BASE_URI__.'modules/colissimo_simplicite/views/img/ajax-loader.gif');
                }
            } else {
                $errors_list[] = $so->getError('999');
            }
        }
        if ($errors_list) {
            $so->context->smarty->assign('error_list', $errors_list);
        }

        $this->setTemplate('module:colissimo_simplicite/views/templates/front/return.tpl');
    }

    public function saveOrderShippingDetails($id_cart, $id_customer, $so_params, $so_object)
    {
        // we want at least one phone number
        $cart = new Cart($id_cart);
        $delivery_address = new Address($cart->id_address_delivery);
        $billing_address = new Address($cart->id_address_invoice);
        $phone_number = $so_params['CEPHONENUMBER'];
        if (!$so_params['CEPHONENUMBER']) {
            if ($delivery_address->phone_mobile) {
                $phone_number = $delivery_address->phone_mobile;
            } elseif ($delivery_address->phone) {
                $phone_number = $delivery_address->phone;
            } elseif ($billing_address->phone_mobile) {
                $phone_number = $billing_address->phone_mobile;
            } elseif ($billing_address->phone) {
                $phone_number = $billing_address->phone;
            } else {
                $phone_number = '';
            }
        }
        // if api use is 3.0 we need to decode for accentued chars
        if (!isset($so_params['CHARSET'])) {
            foreach ($so_params as $key => $value) {
                $so_params[$key] = utf8_decode($value);
            }
        }

        $delivery_mode = array(
            'DOM' => 'Livraison à domicile',
            'BPR' => 'Livraison en Bureau de Poste',
            'A2P' => 'Livraison Commerce de proximité',
            'MRL' => 'Livraison Commerce de proximité',
            'CMT' => 'Livraison commerçants Belgique',
            'CIT' => 'Livraison en Cityssimo',
            'ACP' => 'Agence ColiPoste',
            'CDI' => 'Centre de distribution',
            'BDP' => 'Bureau de poste Belge',
            'RDV' => 'Livraison sur Rendez-vous');

        // default country france
        if (isset($so_params['PRPAYS'])) {
            $country_code = $so_params['PRPAYS'];
        } elseif (isset($so_params['CEPAYS'])) {
            $country_code = $so_params['CEPAYS'];
        } else {
            $country_code = 'FR';
        }
        $id_colissimo_delivery_info = ColissimoDeliveryInfo::getDeliveryInfoExist((int)$id_cart, (int)$id_customer);

        if ((int)$id_colissimo_delivery_info) {
            $colissimo_delivery_info = new ColissimoDeliveryInfo((int)$id_colissimo_delivery_info);
        } else {
            $colissimo_delivery_info = new ColissimoDeliveryInfo();
        }
        $colissimo_delivery_info->id_cart = (int)$id_cart;
        $colissimo_delivery_info->id_customer = (int)$id_customer;
        $colissimo_delivery_info->delivery_mode = pSQL($so_params['DELIVERYMODE']);
        $colissimo_delivery_info->cephonenumber = pSQL($phone_number);

        if ($so_object->delivery_mode == SCFields::RELAY_POINT) {
            isset($so_params['PRID']) ? $colissimo_delivery_info->prid = pSQL($so_params['PRID']) : '';
            isset($so_params['PRNAME']) ? $colissimo_delivery_info->prname = Tools::ucfirst(pSQL($so_params['PRNAME'])) : '';
            isset($delivery_mode[$so_params['DELIVERYMODE']]) ? $colissimo_delivery_info->prfirstname = pSQL($delivery_mode[$so_params['DELIVERYMODE']]) : $colissimo_delivery_info->prfirstname = 'Colissimo';
            isset($so_params['PRCOMPLADRESS']) ? $colissimo_delivery_info->prcompladress = pSQL($so_params['PRCOMPLADRESS']) : '';
            isset($so_params['PRADRESS1']) ? $colissimo_delivery_info->pradress1 = pSQL($so_params['PRADRESS1']) : '';
            isset($so_params['PRADRESS2']) ? $colissimo_delivery_info->pradress2 = pSQL($so_params['PRADRESS2']) : '';
            isset($so_params['PRADRESS3']) ? $colissimo_delivery_info->pradress3 = pSQL($so_params['PRADRESS3']) : '';
            isset($so_params['PRADRESS4']) ? $colissimo_delivery_info->pradress4 = pSQL($so_params['PRADRESS4']) : '';
            isset($so_params['PRZIPCODE']) ? $colissimo_delivery_info->przipcode = pSQL($so_params['PRZIPCODE']) : '';
            isset($so_params['PRTOWN']) ? $colissimo_delivery_info->prtown = pSQL($so_params['PRTOWN']) : '';
            isset($country_code) ? $colissimo_delivery_info->cecountry = pSQL($country_code) : '';
            isset($so_params['CEEMAIL']) ? $colissimo_delivery_info->ceemail = pSQL($so_params['CEEMAIL']) : '';
            isset($so_params['CEDELIVERYINFORMATION']) ? $colissimo_delivery_info->cedeliveryinformation = pSQL($so_params['CEDELIVERYINFORMATION']) : '';
            isset($so_params['CEDOORCODE1']) ? $colissimo_delivery_info->cedoorcode1 = pSQL($so_params['CEDOORCODE1']) : '';
            isset($so_params['CEDOORCODE2']) ? $colissimo_delivery_info->cedoorcode2 = pSQL($so_params['CEDOORCODE2']) : '';
            isset($so_params['CECOMPANYNAME']) ? $colissimo_delivery_info->cecompanyname = pSQL($so_params['CECOMPANYNAME']) : '';
            isset($so_params['CODERESEAU']) ? $colissimo_delivery_info->codereseau = pSQL($so_params['CODERESEAU']) : '';
            isset($so_params['CENAME']) ? $colissimo_delivery_info->cename = pSQL($so_params['CENAME']) : '';
            isset($so_params['CEFIRSTNAME']) ? $colissimo_delivery_info->cefirstname = pSQL($so_params['CEFIRSTNAME']) : '';
            isset($so_params['LOTACHEMINEMENT']) ? $colissimo_delivery_info->lotacheminement = pSQL($so_params['LOTACHEMINEMENT']) : '';
            isset($so_params['DISTRIBUTIONSORT']) ? $colissimo_delivery_info->distributionsort = pSQL($so_params['DISTRIBUTIONSORT']) : '';
            isset($so_params['VERSIONPLANTRI']) ? $colissimo_delivery_info->versionplantri = pSQL($so_params['VERSIONPLANTRI']) : '';
            isset($so_params['DYFORWARDINGCHARGES']) ? $colissimo_delivery_info->dyforwardingcharges = pSQL($so_params['DYFORWARDINGCHARGES']) : '';
        } else {
            isset($so_params['PRID']) ? $colissimo_delivery_info->prid = pSQL($so_params['PRID']) : $colissimo_delivery_info->prid = '';
            isset($so_params['CENAME']) ? $colissimo_delivery_info->prname = Tools::ucfirst(pSQL($so_params['CENAME'])) : '';
            isset($so_params['CEFIRSTNAME']) ? $colissimo_delivery_info->prfirstname = Tools::ucfirst(pSQL($so_params['CEFIRSTNAME'])) : '';
            isset($so_params['CECOMPLADRESS']) ? $colissimo_delivery_info->prcompladress = pSQL($so_params['CECOMPLADRESS']) : '';
            isset($so_params['CEADRESS1']) ? $colissimo_delivery_info->pradress1 = pSQL($so_params['CEADRESS1']) : '';
            isset($so_params['CEADRESS4']) ? $colissimo_delivery_info->pradress2 = pSQL($so_params['CEADRESS4']) : '';
            isset($so_params['CEADRESS3']) ? $colissimo_delivery_info->pradress3 = pSQL($so_params['CEADRESS3']) : '';
            isset($so_params['CEADRESS2']) ? $colissimo_delivery_info->pradress4 = pSQL($so_params['CEADRESS2']) : '';
            isset($so_params['CEZIPCODE']) ? $colissimo_delivery_info->przipcode = pSQL($so_params['CEZIPCODE']) : '';
            isset($so_params['CETOWN']) ? $colissimo_delivery_info->prtown = pSQL($so_params['CETOWN']) : '';
            isset($country_code) ? $colissimo_delivery_info->cecountry = pSQL($country_code) : '';
            isset($so_params['CEEMAIL']) ? $colissimo_delivery_info->ceemail = pSQL($so_params['CEEMAIL']) : '';
            isset($so_params['CEDELIVERYINFORMATION']) ? $colissimo_delivery_info->cedeliveryinformation = pSQL($so_params['CEDELIVERYINFORMATION']) : '';
            isset($so_params['CEDOORCODE1']) ? $colissimo_delivery_info->cedoorcode1 = pSQL($so_params['CEDOORCODE1']) : '';
            isset($so_params['CEDOORCODE2']) ? $colissimo_delivery_info->cedoorcode2 = pSQL($so_params['CEDOORCODE2']) : '';
            isset($so_params['CECOMPANYNAME']) ? $colissimo_delivery_info->cecompanyname = pSQL($so_params['CECOMPANYNAME']) : '';
            isset($so_params['CODERESEAU']) ? $colissimo_delivery_info->codereseau = pSQL($so_params['CODERESEAU']) : '';
            isset($so_params['CENAME']) ? $colissimo_delivery_info->cename = pSQL($so_params['CENAME']) : '';
            isset($so_params['CEFIRSTNAME']) ? $colissimo_delivery_info->cefirstname = pSQL($so_params['CEFIRSTNAME']) : '';
            isset($so_params['LOTACHEMINEMENT']) ? $colissimo_delivery_info->lotacheminement = pSQL($so_params['LOTACHEMINEMENT']) : '';
            isset($so_params['DISTRIBUTIONSORT']) ? $colissimo_delivery_info->distributionsort = pSQL($so_params['DISTRIBUTIONSORT']) : '';
            isset($so_params['VERSIONPLANTRI']) ? $colissimo_delivery_info->versionplantri = pSQL($so_params['VERSIONPLANTRI']) : '';
            isset($so_params['DYFORWARDINGCHARGES']) ? $colissimo_delivery_info->dyforwardingcharges = pSQL($so_params['DYFORWARDINGCHARGES']) : '';
        }
        $colissimo_delivery_info->save();
        return true;
    }
}
