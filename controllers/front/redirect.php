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

class Colissimo_SimpliciteRedirectModuleFrontController extends ModuleFrontController
{

    public $ssl = true;
    public $display_header = false;
    public $display_footer = false;

    /**
     * @see FrontController::initContent()
     */
    public function initContent()
    {
        //parent::initContent();

        $so = new SCfields('API');

        $fields = $so->getFields();

        /* Build back the fields list for SoColissimo, gift infos are send using the JS */
        $inputs = array();
        foreach ($_GET as $key => $value) {
            if (in_array($key, $fields)) {
                $inputs[$key] = trim(Tools::getValue($key));
            }
        }

        /* for belgium number specific format */
        if (Tools::getValue('cePays') == 'BE') {
            if (isset($inputs['cePhoneNumber']) && strpos($inputs['cePhoneNumber'], '324') === 0) {
                $inputs['cePhoneNumber'] = '+324'.Tools::substr($inputs['cePhoneNumber'], 3);
            }
        }
        $param_plus = array(
            /* Get the data set before */
            Tools::getValue('trParamPlus'),
            Tools::getValue('gift'),
            $so->replaceAccentedChars(Tools::getValue('gift_message'))
        );

        $inputs['trParamPlus'] = implode('|', $param_plus);
        /* Add signature to get the gift and gift message in the trParamPlus */
        $inputs['signature'] = $so->generateKey($inputs);
        // automatic settings api protocol for ssl
        $protocol = 'http://';
        if (Configuration::get('PS_SSL_ENABLED')) {
            $protocol = 'https://';
        }

        $colissimo_url = $protocol.Configuration::get('COLISSIMO_URL');
        if ($this->isMobileDevice()) {
            $colissimo_url = $protocol.Configuration::get('COLISSIMO_URL_MOBILE');
        }

        Context::getContext()->smarty->assign(array(
            'inputs' => $inputs,
            'colissimo_url' => $colissimo_url,
            'logo' => Tools::getHttpHost(true).__PS_BASE_URI__.'modules/colissimo_simplicite/logo.gif',
            'loader' => Tools::getHttpHost(true).__PS_BASE_URI__.'modules/colissimo_simplicite/views/img/ajax-loader.gif',
        ));

        $this->setTemplate('module:colissimo_simplicite/views/templates/front/redirect.tpl');
    }

    /**
     * Check if agent user is iPad(for so_mobile)
     * @return bool
     */
    public function isIpad()
    {
        return (bool)strpos($_SERVER['HTTP_USER_AGENT'], 'iPad');
    }

    public function isMobile()
    {
        if (method_exists(Context::getContext()->mobile_detect, 'isMobile')) {
            return (bool)Context::getContext()->mobile_detect->isMobile();
        }
        return false;
    }

    public function isMobileDevice()
    {
        $get_mobile_device = Context::getContext()->getMobileDevice();

        // set api params for 4.0 and mobile
        if ($get_mobile_device || $this->isIpad() || $this->isMobile()) {
            return true;
        }
        return false;
    }
}
