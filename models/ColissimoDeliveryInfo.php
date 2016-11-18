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

class ColissimoDeliveryInfo extends ObjectModel
{

    public $id_colissimo_delivery_info;
    public $id_cart;
    public $id_customer;
    public $delivery_mode;
    public $prid;
    public $prname;
    public $prfirstname;
    public $prcompladress;
    public $pradress1;
    public $pradress2;
    public $pradress3;
    public $pradress4;
    public $przipcode;
    public $prtown;
    public $cecountry;
    public $cephonenumber;
    public $ceemail;
    public $cecompanyname;
    public $cedeliveryinformation;
    public $cedoorcode1;
    public $cedoorcode2;
    public $codereseau;
    public $cename;
    public $cefirstname;
    public $lotacheminement;
    public $distributionsort;
    public $versionplantri;
    public $dyforwardingcharges;
    public static $definition = array(
        'table' => 'colissimo_delivery_info',
        'primary' => 'id_colissimo_delivery_info',
        'multilang' => false,
        'fields' => array(
            'id_cart' => array(
                'type' => ObjectModel :: TYPE_INT,
            ),
            'id_customer' => array(
                'type' => ObjectModel :: TYPE_INT,
            ),
            'delivery_mode' => array(
                'type' => ObjectModel :: TYPE_STRING,
            ),
            'prid' => array(
                'type' => ObjectModel :: TYPE_STRING,
            ),
            'prname' => array(
                'type' => ObjectModel :: TYPE_STRING,
            ),
            'prfirstname' => array(
                'type' => ObjectModel :: TYPE_STRING,
                'validate' => 'isGenericName',
            ),
            'prcompladress' => array(
                'type' => self::TYPE_STRING,
            ),
            'pradress1' => array(
                'type' => self::TYPE_STRING,
            ),
            'pradress2' => array(
                'type' => self::TYPE_STRING,
            ),
            'pradress3' => array(
                'type' => self::TYPE_STRING,
            ),
            'pradress4' => array(
                'type' => self::TYPE_STRING,
            ),
            'przipcode' => array(
                'type' => self::TYPE_STRING,
            ),
            'prtown' => array(
                'type' => self::TYPE_STRING,
            ),
            'cecountry' => array(
                'type' => self::TYPE_STRING,
            ),
            'cephonenumber' => array(
                'type' => self::TYPE_STRING,
            ),
            'ceemail' => array(
                'type' => self::TYPE_STRING,
            ),
            'cecompanyname' => array(
                'type' => self::TYPE_STRING,
            ),
            'cedeliveryinformation' => array(
                'type' => self::TYPE_STRING,
            ),
            'cedoorcode1' => array(
                'type' => self::TYPE_STRING,
            ),
            'cedoorcode2' => array(
                'type' => self::TYPE_STRING,
            ),
            'codereseau' => array(
                'type' => self::TYPE_STRING,
            ),
            'cename' => array(
                'type' => self::TYPE_STRING,
            ),
            'cefirstname' => array(
                'type' => self::TYPE_STRING,
            ),
            'lotacheminement' => array(
                'type' => self::TYPE_STRING,
            ),
            'distributionsort' => array(
                'type' => self::TYPE_STRING,
            ),
            'versionplantri' => array(
                'type' => self::TYPE_STRING,
            ),
            'dyforwardingcharges' => array(
                'type' => self::TYPE_FLOAT,
                'validate' => 'isFloat'
            ),
        )
    );

    public static function getDeliveryInfoExist($id_cart, $id_customer)
    {
        if ((int)$id_cart && (int)$id_customer) {
            return Db::getInstance()->getValue(
                'SELECT id_colissimo_delivery_info FROM '._DB_PREFIX_.'colissimo_delivery_info WHERE
                id_cart = '.(int)$id_cart.' AND id_customer ='.(int)$id_customer
            );
        }
        return false;
    }
}
