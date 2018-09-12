<?php
/**
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
 *  @author    Quadra Informatique <modules@quadra-informatique.fr>
 *  @copyright 2010-2016 La Poste SA
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of La Poste SA
 */

require_once(_PS_MODULE_DIR_.'colissimo_simplicite/models/ColissimoDeliveryInfo.php');

class Colissimo_SimpliciteDeliverymodeModuleFrontController extends ModuleFrontController
{
    public $ssl = false;
    public $display_header = false;
    public $display_footer = false;

    //private $context;

     /**
     * @see FrontController::initContent()
     */
    public function initContent()
    {
    }

    public function postProcess()
    {
        if (Tools::isSubmit('action')) {
            switch (Tools::getValue('action')) {
                case 'isSelected':
                    $isSelected = ColissimoDeliveryinfo::getDeliveryInfoExist($this->context->cart->id, $this->context->customer->id);
                    if($isSelected == '') {
                        echo false;
                        die;
                    }
                    echo true;
                    die;
                    break;
                default:
                    break;
            }
        }
    }
}