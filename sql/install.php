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
 * Do not edit or add to this file if you wish to upgrade PrestaShop to a newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 *  @author    Quadra Informatique <modules@quadra-informatique.fr>
 *  @copyright 2010-2016 La Poste SA
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of La Poste SA
 */

$sql = array();

$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'colissimo_delivery_info` (
            `id_colissimo_delivery_info` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
            `id_cart` int(10) NOT NULL,
            `id_customer` int(10) NOT NULL,
			`delivery_mode` varchar(3) NOT NULL,
			`prid` text(10) NOT NULL,
			`prname` varchar(64) NOT NULL,
			`prfirstname` varchar(64) NOT NULL,
			`prcompladress` text NOT NULL,
			`pradress1` text NOT NULL,
			`pradress2` text NOT NULL,
			`pradress3` text NOT NULL,
			`pradress4` text NOT NULL,
			`przipcode` text(10) NOT NULL,
			`prtown` varchar(64) NOT NULL,
			`cecountry` varchar(10) NOT NULL,
			`cephonenumber` varchar(32) NOT NULL,
			`ceemail` varchar(64) NOT NULL,
			`cecompanyname` varchar(64) NOT NULL,
			`cedeliveryinformation` text NOT NULL,
			`cedoorcode1` varchar(10) NOT NULL,
			`cedoorcode2` varchar(10) NOT NULL,
            `codereseau` varchar(3) NOT NULL,
            `cename` varchar(64) NOT NULL,
			`cefirstname` varchar(64) NOT NULL,
			`lotacheminement` varchar(64) NOT NULL,
			`distributionsort` varchar(64) NOT NULL,
			`versionplantri` text(10) NOT NULL,
			`dyforwardingcharges` decimal(20,6) NOT NULL,
			PRIMARY KEY  (`id_colissimo_delivery_info`)
			) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';

foreach ($sql as $query) {
    if (Db::getInstance()->execute($query) == false) {
        return false;
    }
}
