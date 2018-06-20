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

if (!defined('_PS_VERSION_')) {
    exit;
}
require_once _PS_MODULE_DIR_.'colissimo_simplicite/models/ColissimoDeliveryInfo.php';
require_once(_PS_MODULE_DIR_.'colissimo_simplicite/models/ColissimoDeliveryPoint.php');

class Colissimo_simplicite extends CarrierModule
{

    protected $config_form = false;
    protected $config_single_values_keys = false;
    private $api_num_version = '4.0';
    protected $initial_cost;
    public $url = '';
    private $link_to_img = '';
    private $config = array(
        'name' => 'La Poste - Colissimo Simplicité',
        'id_tax_rules_group' => 0,
        'url' => 'http://www.colissimo.fr/portail_colissimo/suivreResultat.do?parcelnumber=@',
        'active' => true,
        'deleted' => 0,
        'shipping_handling' => false,
        'range_behavior' => 0,
        'is_module' => true,
        'delay' => array(
            'fr' => 'Faites vous livrer en France métropolitaine ou en Europe, dans un des points retrait Colissimo.',
            'en' => 'Do you deliver wherever you want in France.'),
        'id_zone' => 1,
        'shipping_external' => true,
        'external_module_name' => 'colissimo_simplicite',
        'need_range' => true
    );

    public function __construct()
    {
        $this->name = 'colissimo_simplicite';
        $this->tab = 'shipping_logistics';
        $this->version = '4.0.12';
        $this->author = 'Quadra Informatique';
        $this->module_key = '8b991db851bdf7c64ca441f1a4481964';
        $this->need_instance = 1;
        $this->bootstrap = true;
        parent::__construct();
        $this->displayName = $this->l('Colissimo Simplicite');
        $this->description = $this->l('Offer your customer 5 different delivery methods with LaPoste.');
        $this->confirmUninstall = $this->l('Removing the module will also delete the associated carrier');
        $this->ps_versions_compliancy = array(
            'min' => '1.7',
            'max' => _PS_VERSION_);
        $protocol = 'http://';
        $this->link_to_img = $this->_path.'/views/img/';
        if (Configuration::get('PS_SSL_ENABLED') && Configuration::get('PS_SSL_ENABLED_EVERYWHERE')) {
            $protocol = 'https://';
        }
        $this->url = $protocol.Tools::getShopDomainSsl().__PS_BASE_URI__.'modules/'.$this->name.'/validation.php';
        if (self::isInstalled($this->name)) {
            $warning = array();
            $so_carrier = new Carrier(Configuration::get('COLISSIMO_CARRIER_ID'));
            if (Validate::isLoadedObject($so_carrier)) {
                if (!$this->checkZone((int)$so_carrier->id)) {
                    $warning[] .= $this->l('\'Carrier Zone(s)\'').' ';
                }
                if (!$this->checkGroup((int)$so_carrier->id)) {
                    $warning[] .= $this->l('\'Carrier Group\'').' ';
                }
                if (!$this->checkRange((int)$so_carrier->id)) {
                    $warning[] .= $this->l('\'Carrier Range(s)\'').' ';
                }
                if (!$this->checkDelivery((int)$so_carrier->id)) {
                    $warning[] .= $this->l('\'Carrier price delivery\'').' ';
                }
            }

            //Check config and display warning
            if (!Configuration::get('COLISSIMO_ID')) {
                $warning[] .= $this->l('\'Id FO\'').' ';
            }
            if (!Configuration::get('COLISSIMO_KEY')) {
                $warning[] .= $this->l('\'Key\'').' ';
            }
            if (!Configuration::get('COLISSIMO_URL')) {
                $warning[] .= $this->l('\'Url So\'').' ';
            }

            if (count($warning)) {
                $this->warning .= implode(' , ', $warning).$this->l('must be configured to use this module correctly').' ';
            }
        }
        $this->config_single_values_keys = array(
            'COLISSIMO_CARRIER_ID',
            'COLISSIMO_ID',
            'COLISSIMO_KEY',
            'COLISSIMO_URL',
            'COLISSIMO_URL_MOBILE',
            'COLISSIMO_URL_POINTDERETRAIT',
            'COLISSIMO_WS_URL',
            'COLISSIMO_OVERCOST',
            'COLISSIMO_COST_SELLER',
            'COLISSIMO_SELLER_AMOUNT',
            'COLISSIMO_UPG_COUNTRY',
            'COLISSIMO_PREPARATION_TIME',
            'COLISSIMO_CARRIER_ID',
            'COLISSIMO_MIN_COST',
            'COLISSIMO_SUP',
            'COLISSIMO_SUP_URL',
            'COLISSIMO_OVERCOST_TAX',
            'COLISSIMO_PERSONAL_PHONE',
            'COLISSIMO_PERSONAL_ZIP_CODE',
            'COLISSIMO_PERSONAL_QUANTITIES',
            'COLISSIMO_PERSONAL_SIRET',
            'COLISSIMO_PERSONAL_DATA',
            'COLISSIMO_SELLER_IMPACT',
            'COLISSIMO_USE_POINTDERETRAIT',
            'COLISSIMO_LOGIN',
            'COLISSIMO_PASSWORD'
        );
        $this->config_single_values_keys_exception = array(
            'COLISSIMO_PERSONAL_PHONE',
            'COLISSIMO_PERSONAL_ZIP_CODE',
            'COLISSIMO_PERSONAL_QUANTITIES',
            'COLISSIMO_PERSONAL_SIRET',
            'COLISSIMO_PERSONAL_DATA',
            'COLISSIMO_PERSONAL_ACCEPT'
        );
    }

    public function install()
    {
        if (extension_loaded('curl') == false) {
            $this->_errors[] = $this->l('You have to enable the cURL extension on your server to install this module');
            return false;
        }
        $carrier = $this->addCarrier();
        $this->addZones($carrier);
        $this->addGroups($carrier);
        $this->addRanges($carrier);
        if (!Configuration::updateValue('COLISSIMO_ID', null) ||
            !Configuration::updateValue('COLISSIMO_KEY', null) ||
            !Configuration::updateValue('COLISSIMO_URL', 'ws.colissimo.fr/pudo-fo-frame/storeCall.do') ||
            !Configuration::updateValue('COLISSIMO_URL_MOBILE', 'ws-mobile.colissimo.fr/') ||
            !Configuration::updateValue('COLISSIMO_PREPARATION_TIME', 1) ||
            !Configuration::updateValue('COLISSIMO_OVERCOST', 3.6) ||
            !Configuration::updateValue('COLISSIMO_COST_SELLER', false) ||
            !Configuration::updateValue('COLISSIMO_SELLER_AMOUNT', 0) ||
            !Configuration::updateValue('COLISSIMO_SELLER_IMPACT', 0) ||
            !Configuration::updateValue('COLISSIMO_SUP_URL', 'ws.colissimo.fr/supervision-pudo-frame/supervision.jsp') ||
            !Configuration::updateValue('COLISSIMO_SUP', true) ||
            !Configuration::updateValue('COLISSIMO_TOKEN_POINTDERETRAIT', '') ||
            !Configuration::updateValue('COLISSIMO_PDR_TOKEN_HOUR', '') ||
            !Configuration::updateValue('COLISSIMO_USE_POINTDERETRAIT', '1') ||
            !Configuration::updateValue('COLISSIMO_URL_POINTDERETRAIT', 'ws.colissimo.fr/widget-point-retrait/rest/authenticate.rest') ||
            !Configuration::updateValue('COLISSIMO_WS_URL', 'ws.colissimo.fr/') ||
            !Configuration::updateValue('COLISSIMO_LOGIN', null) ||
            !Configuration::updateValue('COLISSIMO_PASSWORD', null)
        ) {
            return false;
        }
        include(dirname(__FILE__).'/sql/install.php');
        return parent::install() &&
            $this->registerHook('header') &&
            $this->registerHook('backOfficeHeader') &&
            $this->registerHook('updateCarrier') &&
            $this->registerHook('actionValidateOrder') &&
            $this->registerHook('actionCarrierUpdate') &&
            $this->registerHook('displayAdminOrder') &&
            $this->registerHook('displayCarrierExtraContent');
    }

    public function uninstall()
    {
        $so_id = (int)Configuration::get('COLISSIMO_CARRIER_ID');
        //Configuration::deleteByName('COLISSIMO_ID');
        //Configuration::deleteByName('COLISSIMO_KEY');
        Configuration::deleteByName('COLISSIMO_URL');
        Configuration::deleteByName('COLISSIMO_URL_MOBILE');
        Configuration::deleteByName('COLISSIMO_OVERCOST');
        Configuration::deleteByName('COLISSIMO_COST_SELLER');
        Configuration::deleteByName('COLISSIMO_SELLER_AMOUNT');
        Configuration::deleteByName('COLISSIMO_SELLER_IMPACT');
        Configuration::deleteByName('COLISSIMO_UPG_COUNTRY');
        Configuration::deleteByName('COLISSIMO_PREPARATION_TIME');
        Configuration::deleteByName('COLISSIMO_CARRIER_ID');
        Configuration::deleteByName('COLISSIMO_SUP');
        Configuration::deleteByName('COLISSIMO_SUP_URL');
        Configuration::deleteByName('COLISSIMO_OVERCOST_TAX');

        $carrier = new Carrier($so_id);
        $carrier->delete();
        include(dirname(__FILE__).'/sql/uninstall.php');

        return parent::uninstall();
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        if (((bool)Tools::isSubmit('submitColissimo_simpliciteModule')) == true) {
            $this->postProcess();
        }

        $this->context->smarty->assign(array(
            'module_dir' => $this->_path,
            'colissimo_version' => $this->version
        ));

        $output = $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');

        if (!Configuration::get('COLISSIMO_PERSONAL_DATA')) {
            $output .= $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure_warning.tpl');
        }

        return $output.$this->renderForm();
    }

    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitColissimo_simpliciteModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );
        return $helper->generateForm(array(
                array(
                    'form' => $this->getConfigForm()
                )
        ));
    }

    /**
     * Create the structure of your form.
     */
    protected function getConfigForm()
    {
        $form = array(
            'legend' => array(
                'title' => $this->l('Colissimo Simplicity').' V'.$this->version,
                'icon' => 'icon-cogs',
            ),
            'submit' => array(
                'title' => $this->l('Save'),
            )
        );
        //======================================================================
        // INFO TAB
        $form['tabs']['about'] = $this->l('About Colissimo Informations');

        // Check current language
        $language = new Language($this->context->language->id);
        $defaut_tpl = $this->local_path.'views/templates/admin/about_fr.tpl';
        if ($language->iso_code != 'fr') {
            $defaut_tpl = $this->local_path.'views/templates/admin/about_en.tpl';
        }
        $form['input'][] = array(
            'tab' => 'about',
            'type' => 'html',
            'name' => 'about',
            'html_content' => $this->context->smarty->fetch($defaut_tpl),
        );

        //======================================================================
        // CREDENTIALS TAB
        $form['tabs']['credentials'] = $this->l('Merchant Informations');

        $form['input'][] = array(
            'tab' => 'credentials',
            'type' => 'text',
            'col' => 2,
            'required' => true,
            'label' => $this->l('Phone number'),
            'name' => 'COLISSIMO_PERSONAL_PHONE',
            'desc' => $this->l('Example  0144183004'),
        );
        $form['input'][] = array(
            'tab' => 'credentials',
            'type' => 'text',
            'col' => 2,
            'required' => true,
            'label' => $this->l('Zip code'),
            'name' => 'COLISSIMO_PERSONAL_ZIP_CODE',
            'desc' => $this->l('Example  92300'),
        );
        $form['input'][] = array(
            'tab' => 'credentials',
            'type' => 'select',
            'required' => true,
            'label' => $this->l('Mean number of parcels'),
            'name' => 'COLISSIMO_PERSONAL_QUANTITIES',
            'options' => array(
                'query' => array(
                    array(
                        'id' => '< 250 colis / mois',
                        'name' => $this->l('< 250 colis / mois')
                    ),
                    array(
                        'id' => '> 250 colis / mois',
                        'name' => $this->l('> 250 colis / mois')
                    ),
                ),
                'id' => 'id',
                'name' => 'name'
            ),
        );
        $form['input'][] = array(
            'tab' => 'credentials',
            'type' => 'text',
            'col' => 2,
            'required' => true,
            'label' => $this->l('Siret'),
            'name' => 'COLISSIMO_PERSONAL_SIRET',
            'desc' => $this->l('Siret is 14 number'),
        );
        $form['input'][] = array(
            'tab' => 'credentials',
            'type' => 'checkbox',
            'required' => true,
            'label' => $this->l('Terms & conditions'),
            'name' => 'COLISSIMO_PERSONAL',
            'desc' => $this->l('In case of refusal, you can sent an email at the following address').
            ' : <a style="color: #268ccd;" href="mailto: modules-prestashop@laposte.fr">modules-prestashop@laposte.fr</a>',
            'values' => array(
                'query' => array(
                    array(
                        'id' => 'ACCEPT',
                        'name' => $this->l('I accept that informations concerning the number of parcels are sent to our partner La poste - Colissimo'),
                        'val' => 1
                    ),
                ),
                'id' => 'id',
                'name' => 'name',
            )
        );


        //======================================================================
        // GENERAL TAB
        $form['tabs']['general'] = $this->l('Your Colissimo Box');

        $form['input'][] = array(
            'tab' => 'general',
            'col' => 3,
            'type' => 'text',
            'required' => true,
            'label' => $this->l('Encryption key'),
            'name' => 'COLISSIMO_KEY',
            'desc' => $this->l('Available in your ').' <a href="https://www.colissimo.entreprise.laposte.fr" target="_blank" >Colissimo Box </a>'.'<br/>'.
            $this->l('by using the menu "Applications > Delivery > Choice of delivery methods" ')
        );
        $form['input'][] = array(
            'tab' => 'general',
            'col' => 3,
            'type' => 'text',
            'required' => true,
            'label' => $this->l('Front Office Identifier'),
            'name' => 'COLISSIMO_ID',
            'desc' => $this->l('Available in your ').' <a href="https://www.colissimo.entreprise.laposte.fr" target="_blank" >Colissimo Box </a>'.'<br/>'.
            $this->l('by using the menu "Applications > Delivery > Choice of delivery methods" ')
        );
        $form['input'][] = array(
            'tab' => 'general',
            'col' => 3,
            'type' => 'text',
            'required' => true,
            'label' => $this->l('Order Preparation time'),
            'suffix' => $this->l('Day(s)'),
            'name' => 'COLISSIMO_PREPARATION_TIME',
            'desc' => $this->l('Business days from Monday to Friday').
            '<br/>'.$this->l('Must be the same parameter as in your ').
            ' <a href="https://www.colissimo.entreprise.laposte.fr" target="_blank" >Colissimo Box </a>'
        );
        $form['input'][] = array(
            'tab' => 'general',
            'type' => 'html',
            'name' => 'url_note',
            'html_content' => '<hr/><strong>'.$this->l('Please fill in these two addresses in your').
            ' <a href="https://www.colissimo.entreprise.laposte.fr" target="_blank" >Colissimo Box </a></strong><ul>'.
            '<li>'.$this->l('In the "Delivery options selection page"').'</li>'.
            '<li>'.$this->l('In the "Delivery options selection page (mobile version)"').'</li></ul>',
        );

        $form['input'][] = array(
            'tab' => 'general',
            'type' => 'free',
            'label' => $this->l('When the customer has successfully selected the delivery method (Validation)'),
            'name' => 'VALIDATION_URL',
        );
        $form['input'][] = array(
            'tab' => 'general',
            'type' => 'free',
            'label' => $this->l('When the client could not select the delivery method (Failed)'),
            'name' => 'RETURN_URL',
        );

        //======================================================================
        // SYSTEM TAB
        $form['tabs']['system'] = $this->l('Colissimo simplicity system parameters');

        $form['input'][] = array(
            'tab' => 'system',
            'col' => 3,
            'type' => 'text',
            'required' => true,
            'label' => $this->l('Url of back office Colissimo.'),
            'name' => 'COLISSIMO_URL',
            'desc' => $this->l('Url of back office Colissimo.')
        );
        $form['input'][] = array(
            'tab' => 'system',
            'col' => 3,
            'type' => 'text',
            'required' => true,
            'label' => $this->l('Url So Mobile'),
            'name' => 'COLISSIMO_URL_MOBILE',
            'desc' => $this->l('Url of back office Colissimo Mobile. Customers with smartphones or ipad will be redirect there. Warning, this url do not allow delivery in belgium ')
        );
        $form['input'][] = array(
            'tab' => 'system',
            'col' => 3,
            'type' => 'text',
            'required' => true,
            'label' => $this->l('Url web service la poste'),
            'name' => 'COLISSIMO_WS_URL',
            'desc' => $this->l('Url of web service la poste')
        );    
        $form['input'][] = array(
            'tab' => 'system',
            'col' => 3,
            'type' => 'text',
            'required' => true,
            'label' => $this->l('Url authentification Point de retrait'),
            'name' => 'COLISSIMO_URL_POINTDERETRAIT',
            'desc' => $this->l('Url of point de retrait option')
        );   
        $form['input'][] = array(
            'tab' => 'system',
            'type' => 'switch',
            'label' => $this->l('Supervision'),
            'name' => 'COLISSIMO_SUP',
            'is_bool' => true,
            'required' => true,
            'desc' => $this->l('Enable or disable the check availability  of Colissimo service.'),
            'values' => array(
                array(
                    'id' => 'active_on',
                    'value' => true,
                    'label' => $this->l('Enabled')
                ),
                array(
                    'id' => 'active_off',
                    'value' => false,
                    'label' => $this->l('Disabled')
                )
            ),
        );
        $form['input'][] = array(
            'tab' => 'system',
            'col' => 3,
            'type' => 'text',
            'required' => true,
            'label' => $this->l('Url Supervision'),
            'name' => 'COLISSIMO_SUP_URL',
            'desc' => $this->l('The monitor URL is to ensure the availability of the socolissimo service. We strongly recommend that you do not disable it')
        );

        //======================================================================
        // PRESTASHOP TAB
        $form['tabs']['prestashop'] = $this->l('Colissimo simplicity prestashop parameters');

        $form['input'][] = array(
            'tab' => 'prestashop',
            'type' => 'select',
            'required' => true,
            'label' => $this->l('Home carrier'),
            'name' => 'COLISSIMO_CARRIER_ID',
            'options' => array(
                'query' => Carrier::getCarriers($this->context->language->id, true, false, false, null, Carrier::ALL_CARRIERS),
                'id' => 'id_carrier',
                'name' => 'name'
            ),
            'desc' => $this->l('Carrier used to get "Colissimo at home" cost')
        );
        $form['input'][] = array(
            'tab' => 'prestashop',
            'type' => 'switch',
            'col' => 4,
            'label' => $this->l('Withdrawal point cost'),
            'name' => 'COLISSIMO_COST_SELLER',
            'is_bool' => true,
            'required' => true,
            'desc' => $this->l('This cost override the normal cost for seller delivery.'),
            'values' => array(
                array(
                    'id' => 'active_on',
                    'value' => true,
                    'label' => $this->l('Enabled')
                ),
                array(
                    'id' => 'active_off',
                    'value' => false,
                    'label' => $this->l('Disabled')
                )
            ),
        );
        $form['input'][] = array(
            'tab' => 'prestashop',
            'col' => 4,
            'type' => 'text',
            'required' => true,
            'label' => $this->l('Withdrawal cost'),
            'name' => 'COLISSIMO_SELLER_AMOUNT',
            'desc' => $this->l('Withdrawal cost for "Colissimo at a withdrawal point"')
        );
        $form['input'][] = array(
            'tab' => 'prestashop',
            'col' => 3,
            'type' => 'radio',
            'label' => $this->l('Price impact'),
            'name' => 'COLISSIMO_SELLER_IMPACT',
            'required' => false,
            'desc' => $this->l('Choose your impact on the price for Withdrawal cost'),
            'values' => array(
                array
                    (
                    'id' => 'price_down',
                    'value' => 0,
                    'label' => $this->l('Down the price')
                ),
                array(
                    'id' => 'price_up',
                    'value' => 1,
                    'label' => $this->l('Up the price')
                ),
            )
        );
        //======================================================================
        // WITHDRAWAL TAB

        $form['tabs']['withdrawal'] = $this->l('Front page Withdrawal');


        $form['input'][] = array(
            'tab' => 'withdrawal',
            'type' => 'switch',
            'label' => $this->l('Activate Colissimo withdrawal point, free option'),
            'name' => 'COLISSIMO_USE_POINTDERETRAIT',
            'is_bool' => true,
            'required' => true,
            'desc' => $this->l('Enable Colissimo withdrawal point, if you have Profesionnal contrat, disable this'),
            'values' => array(
                array(
                    'id' => 'active_on',
                    'value' => '1',
                    'label' => $this->l('Enabled')
                ),
                array(
                    'id' => 'active_off',
                    'value' => '0',
                    'label' => $this->l('Disabled')
                )
            ),
        );


        $form['input'][] = array(
            'tab' => 'withdrawal',
            'col' => 3,
            'type' => 'text',
            'required' => false,
            'id' => 'colissimo_login',
            'label' => $this->l('Login Colissimo'),
            'name' => 'COLISSIMO_LOGIN',
            'desc' => $this->l('Your connexion login at(only for free option) ').' <a href="https://www.colissimo.entreprise.laposte.fr" target="_blank" >Colissimo Box </a>'
        );

        $form['input'][] = array(
            'tab' => 'withdrawal',
            'col' => 3,
            'type' => 'text',
            'required' => false,
            'id' => 'colissimo_password',
            'label' => $this->l('Password Colissimo'),
            'name' => 'COLISSIMO_PASSWORD',
            'desc' => $this->l('Your connexion password at(only for free option) ').' <a href="https://www.colissimo.entreprise.laposte.fr" target="_blank" >Colissimo Box </a>'
        );

        $form['input'][] = array(
            'tab' => 'withdrawal',
            'col' => 3,
            'type' => 'text',
            'required' => true,
            'label' => $this->l('Order Preparation time'),
            'suffix' => $this->l('Day(s)'),
            'name' => 'COLISSIMO_PREPARATION_TIME',
            'desc' => $this->l('Business days from Monday to Friday').
            '<br/>'.$this->l('Must be the same parameter as in your ').
            ' <a href="https://www.colissimo.entreprise.laposte.fr" target="_blank" >Colissimo Box </a>'
        );
        return $form;
    }

    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues()
    {
        $return = array();
        foreach ($this->config_single_values_keys as $key) {
            $return[$key] = Configuration::get($key);
        }

        $return['VALIDATION_URL'] = '<p class="form-control-static">'.htmlentities($this->url, ENT_NOQUOTES, 'UTF-8').'</p>';
        $return['RETURN_URL'] = '<p class="form-control-static">'.htmlentities($this->url, ENT_NOQUOTES, 'UTF-8').'</p>';

        return $return;
    }

    protected function savePreactivationRequest()
    {
        $employee = new Employee((int)Context::getContext()->cookie->id_employee);

        $data = array(
            'iso_lang' => Tools::strtolower($this->context->language->iso_code),
            'iso_country' => Tools::strtoupper($this->context->country->iso_code),
            'host' => $_SERVER['HTTP_HOST'],
            'ps_version' => _PS_VERSION_,
            'ps_creation' => _PS_CREATION_DATE_,
            'partner' => $this->name,
            'firstname' => $employee->firstname,
            'lastname' => $employee->lastname,
            'email' => $employee->email,
            'shop' => Configuration::get('PS_SHOP_NAME'),
            'type' => 'home',
            'phone' => Configuration::get('COLISSIMO_PERSONAL_PHONE'),
            'zipcode' => Configuration::get('COLISSIMO_PERSONAL_ZIP_CODE'),
            'fields' => serialize(
                array(
                    'quantities' => Configuration::get('COLISSIMO_PERSONAL_QUANTITIES'),
                    'siret' => Configuration::get('COLISSIMO_PERSONAL_SIRET'),
                )
            ),
        );

        $query = http_build_query($data);
        return @Tools::file_get_contents('http://api.prestashop.com/partner/premium/set_request.php?'.$query);
    }

    /**
     * Save form data.
     */
    protected function postProcess()
    {
        if (Tools::getValue('COLISSIMO_ID') == null && (!Configuration::get('COLISSIMO_USE_POINTDERETRAIT')) && (Tools::getValue('COLISSIMO_USE_POINTDERETRAIT') == 0)) {
            $this->context->controller->errors[] = $this->l('ID SO not specified');
        }

        if (Tools::getValue('COLISSIMO_KEY') == null && (Configuration::get('COLISSIMO_USE_POINTDERETRAIT') == 0) && (Tools::getValue('COLISSIMO_USE_POINTDERETRAIT') == 0)) {
            $this->context->controller->errors[] = $this->l('Key SO not specified');
        }
        if (Tools::getValue('COLISSIMO_LOGIN') == null && (Configuration::get('COLISSIMO_USE_POINTDERETRAIT') == 1) && (Tools::getValue('COLISSIMO_USE_POINTDERETRAIT') == 1 )) {
            $this->context->controller->errors[] = $this->l('Login Colissimo is not specified, required for free option');
        }
        if (Tools::getValue('COLISSIMO_PASSWORD') == null && (Configuration::get('COLISSIMO_USE_POINTDERETRAIT') == 1) && (Tools::getValue('COLISSIMO_USE_POINTDERETRAIT') == 1 )) {
            $this->context->controller->errors[] = $this->l('Pass Colissimo is not specified, required for free option');
        }
        if (Tools::getValue('COLISSIMO_PREPARATION_TIME') == null) {
            $this->context->controller->errors[] = $this->l('Preparation time not specified');
        } elseif (!Validate::isInt(Tools::getValue('COLISSIMO_PREPARATION_TIME'))) {
            $this->context->controller->errors[] = $this->l('Invalid preparation time');
        }

        if (Tools::getValue('COLISSIMO_URL') == null) {
            $this->context->controller->errors[] = $this->l('Front URL is not specified');
        }
        if (Tools::getValue('COLISSIMO_URL_MOBILE') == null) {
            $this->context->controller->errors[] = $this->l('Front mobile URL is not specified');
        }
        if (Tools::getValue('COLISSIMO_SUP_URL') == null) {
            $this->context->controller->errors[] = $this->l('Supervision URL is not specified');
        }

        if (!count($this->context->controller->errors)) {
            // re allocation id socolissimo if needed
            if ((int)Tools::getValue('COLISSIMO_CARRIER_ID') != (int)Configuration::get('COLISSIMO_CARRIER_ID')) {
                Configuration::updateValue(
                    'COLISSIMO_CARRIER_ID',
                    (int)Tools::getValue('COLISSIMO_CARRIER_ID')
                );
                $this->reallocationCarrier((int)Configuration::get('COLISSIMO_CARRIER_ID'));
            }
            foreach ($this->config_single_values_keys as $key) {
                if (!array_search($key, $this->config_single_values_keys_exception)) {
                    Configuration::updateValue($key, Tools::getValue($key));
                }
            }
        }
        $reload_credit = false;

        if (Configuration::get('COLISSIMO_PERSONAL_DATA')) {
            if (Tools::getValue('COLISSIMO_PERSONAL_PHONE') && (Tools::getValue('COLISSIMO_PERSONAL_PHONE') != Configuration::get('COLISSIMO_PERSONAL_PHONE'))) {
                $reload_credit = true;
            }
            if (Tools::getValue('COLISSIMO_PERSONAL_ZIP_CODE') && (Tools::getValue('COLISSIMO_PERSONAL_ZIP_CODE') != Configuration::get('COLISSIMO_PERSONAL_ZIP_CODE'))) {
                $reload_credit = true;
            }
            if (Tools::getValue('COLISSIMO_PERSONAL_QUANTITIES') && (Tools::getValue('COLISSIMO_PERSONAL_QUANTITIES') != Configuration::get('COLISSIMO_PERSONAL_QUANTITIES'))) {
                $reload_credit = true;
            }
            if (Tools::getValue('COLISSIMO_PERSONAL_SIRET') && (Tools::getValue('COLISSIMO_PERSONAL_SIRET') != Configuration::get('COLISSIMO_PERSONAL_SIRET'))) {
                $reload_credit = true;
            }
        }

        if (!Configuration::get('COLISSIMO_PERSONAL_DATA') || $reload_credit) {
            if (!(bool)preg_match('#^(([\d]{2})([\s]){0,1}){5}$#', Tools::getValue('COLISSIMO_PERSONAL_PHONE'))) {
                $this->context->controller->errors[] = $this->l('Phone number is incorrect');
            }
            if (!(bool)preg_match('#^(([0-8][0-9])|(9[0-5]))[0-9]{3}$#', Tools::getValue('COLISSIMO_PERSONAL_ZIP_CODE'))) {
                $this->context->controller->errors[] = $this->l('Zip code is incorrect');
            }
            if (!Tools::getValue('COLISSIMO_PERSONAL_QUANTITIES')) {
                $this->context->controller->errors[] = $this->l('Mean number is incorrect');
            }
            if (!$this->isSiret(Tools::getValue('COLISSIMO_PERSONAL_SIRET'))) {
                $this->context->controller->errors[] = $this->l('Siret is incorrect');
            }
            if (!Tools::getValue('COLISSIMO_PERSONAL_ACCEPT')) {
                $this->context->controller->errors[] = $this->l('You must accept terms and conditions');
            }
            if (!count($this->context->controller->errors)) {
                Configuration::updateValue('COLISSIMO_PERSONAL_PHONE', Tools::getValue('COLISSIMO_PERSONAL_PHONE'));
                Configuration::updateValue('COLISSIMO_PERSONAL_ZIP_CODE', Tools::getValue('COLISSIMO_PERSONAL_ZIP_CODE'));
                Configuration::updateValue('COLISSIMO_PERSONAL_QUANTITIES', Tools::getValue('COLISSIMO_PERSONAL_QUANTITIES'));
                Configuration::updateValue('COLISSIMO_PERSONAL_SIRET', Tools::getValue('COLISSIMO_PERSONAL_SIRET'));
                Configuration::updateValue('COLISSIMO_PERSONAL_ACCEPT', Tools::getValue('COLISSIMO_PERSONAL_ACCEPT'));
                Configuration::updateValue('COLISSIMO_USE_POINTDERETRAIT', Tools::getValue('COLISSIMO_USE_POINTDERETRAIT'));
                if ($this->savePreactivationRequest()) {
                    Configuration::updateValue('COLISSIMO_PERSONAL_DATA', 1);
                }
            }
        }
    }

    public function getOrderShippingCost($params, $shipping_cost)
    {
        // for order in BO
        if (!$this->context->cart instanceof Cart || !$this->context->cart->id) {
            $this->context->cart = new Cart($params->id);
        }
        // check colissimopass module installed and used
        if (Module::isEnabled('colissimopass')) {
            // is user connect ?
            require_once(_PS_MODULE_DIR_.'colissimopass/classes/ColissimoPassUser.php');
            if (ColissimoPassUser::isActive()) {
                return 0;
            }
            // is product pass in cart ?
            $cart = $this->context->cart;
            $products = $cart->getProducts();
            if (is_array($products)) {
                foreach ($products as $product) {
                    if ($product['id_product'] == (int)Configuration::get('ID_COLISSIMO_PASS_PDT')) {
                        return 0;
                    }
                }
            }
        }

        if (!$this->initial_cost) {
            $this->initial_cost = $shipping_cost;
        }

        // check api already return a shipping cost ?
        $api_price = $this->getApiPrice((int)$this->context->cart->id);

        if ($api_price) {
            $carrier_colissimo = new Carrier((int)Configuration::get('COLISSIMO_CARRIER_ID'));
            $address = new Address((int)$this->context->cart->id_address_delivery);
            $tax = $carrier_colissimo->getTaxesRate($address);

            // must retrieve the price without tax if needed
            if ($tax) {
                (float)$tax_rate = ((float)$tax / 100) + 1;
                $api_price = (float)$api_price / (float)$tax_rate;
            }
            return (float)$api_price;
        }
        return $shipping_cost;
    }

    public function getOrderShippingCostExternal($params)
    {
        return true;
    }

    protected function addCarrier()
    {
        $carrier = new Carrier();
        $carrier->name = $this->config['name'];
        $carrier->id_tax_rules_group = $this->config['id_tax_rules_group'];
        $carrier->id_zone = $this->config['id_zone'];
        $carrier->url = $this->config['url'];
        $carrier->active = $this->config['active'];
        $carrier->deleted = $this->config['deleted'];
        $carrier->shipping_handling = $this->config['shipping_handling'];
        $carrier->range_behavior = $this->config['range_behavior'];
        $carrier->is_module = $this->config['is_module'];
        $carrier->shipping_external = $this->config['shipping_external'];
        $carrier->external_module_name = $this->config['external_module_name'];
        $carrier->need_range = $this->config['need_range'];

        foreach (Language::getLanguages() as $lang) {
            if ($lang['iso_code'] == 'fr') {
                $carrier->delay[$lang['id_lang']] = $this->config['delay'][$lang['iso_code']];
            }
            if ($lang['iso_code'] == 'en') {
                $carrier->delay[$lang['id_lang']] = $this->config['delay'][$lang['iso_code']];
            }
        }
        if (Shop::isFeatureActive()) {
            Shop::setContext(Shop::CONTEXT_ALL);
        }

        if ($carrier->add() == true) {
            @copy(dirname(__FILE__).'/views/img/colissimo.jpg', _PS_SHIP_IMG_DIR_.'/'.(int)$carrier->id.'.jpg');
            Configuration::updateValue('COLISSIMO_CARRIER_ID', (int)$carrier->id);
            return $carrier;
        }

        return false;
    }

    protected function addGroups($carrier)
    {
        $groups_ids = array();
        $groups = Group::getGroups(Context::getContext()->language->id);
        foreach ($groups as $group) {
            $groups_ids[] = $group['id_group'];
        }

        $carrier->setGroups($groups_ids);
    }

    protected function addRanges($carrier)
    {
        $range_price = new RangePrice();
        $range_price->id_carrier = $carrier->id;
        $range_price->delimiter1 = '0';
        $range_price->delimiter2 = '10000';
        $range_price->add();

        $range_weight = new RangeWeight();
        $range_weight->id_carrier = $carrier->id;
        $range_weight->delimiter1 = '0';
        $range_weight->delimiter2 = '10000';
        $range_weight->add();
    }

    protected function addZones($carrier)
    {
        $zones = Zone::getZones();

        foreach ($zones as $zone) {
            $carrier->addZone($zone['id_zone']);
        }
    }

    /**
     * Add the CSS & JavaScript files you want to be loaded in the BO.
     */
    public function hookBackOfficeHeader()
    {
        $this->context->controller->addCSS($this->_path.'views/css/back.css');
    }

    /**
     * Add the CSS & JavaScript files you want to be added on the FO.
     */
    public function hookHeader()
    {
        if (isset($this->context->controller->page_name) && $this->context->controller->page_name == "checkout") {
            if (Configuration::get('COLISSIMO_USE_POINTDERETRAIT')) {
                $this->context->controller->addJS($this->_path.'views/js/jquery.frameColiposte.js');
                $this->context->controller->addJS($this->_path.'views/js/front_delivery_point.js');
            } else {
                $this->context->controller->addJS($this->_path.'/views/js/front.js');
                $this->context->controller->addJqueryPlugin(array(
                    'fancybox'));
            }
        } else {
            $this->context->controller->addJS($this->_path.'/views/js/redirect.js');
        }
        $this->context->controller->addCSS($this->_path.'/views/css/front.css');
    }

    public function hookActionValidateOrder($params)
    {
        if ($params['order']->id_carrier != Configuration::get('COLISSIMO_CARRIER_ID')) {
            return;
        }

        $order = $params['order'];
        if (Configuration::get('COLISSIMO_USE_POINTDERETRAIT')) {
            $order->id_address_delivery = $this->isExist(
                (int)$order->id_address_delivery,
                (int)$order->id_cart,
                (int)$order->id_customer
            );
        } else {
            $order->id_address_delivery = $this->isSameAddress((int)$order->id_address_delivery, (int)$order->id_cart, (int)$order->id_customer);
        }
        $order->update();
        Configuration::updateValue('COLISSIMO_CONFIGURATION_OK', true);

        if (Module::isEnabled('colissimopass')) {
            // is user connect ?
            require_once(_PS_MODULE_DIR_.'colissimopass/colissimopass.php');
            if (ColissimoPassUser::isActive()) {
                Colissimopass::sendConsignment($order);
            }
        }
    }

    public function getDeliveryInfosPointderetrait($id_cart, $id_customer)
    {
        return Db::getInstance()->getRow(
            'SELECT * FROM '._DB_PREFIX_.'colissimo_delivery_point
            WHERE id_cart = '.(int)$id_cart.' AND id_customer = '.(int)$id_customer
        );
    }

    public function isExist($id_address, $id_cart, $id_customer)
    {

        $id_socolissimo_deliverypoint_info_exist = ColissimoDeliveryPoint::alreadyExists((int)$id_cart, (int)$id_customer);

        $return = Db::getInstance()->getRow(
            'SELECT * FROM '._DB_PREFIX_.'colissimo_delivery_point
	    WHERE id_cart =\''.(int)$id_cart.'\' AND id_customer =\''.(int)$id_customer.'\''
        );

        if (!$return) {
            return $id_address;
        }

        $socolissimo_deliverypoint_info = new ColissimoDeliveryPoint((int)$id_socolissimo_deliverypoint_info_exist);
        $new_address = new Address();
        $customer = new Customer($id_customer);
        $sql = Db::getInstance()->getRow('SELECT c.id_country, cl.name FROM '._DB_PREFIX_.'country c
                                          LEFT JOIN '._DB_PREFIX_.'country_lang cl ON cl.id_lang = '.(int)$this->context->language->id.'
                                          AND cl.id_country = c.id_country WHERE iso_code = "'.pSQL($socolissimo_deliverypoint_info->codePays).'"');
        $iso_code = $sql['id_country'];
        $new_address->id_customer = (int)$id_customer;
        $new_address->firstname = preg_replace('/\d/', '', Tools::substr($customer->firstname, 0, 32));
        $new_address->lastname = preg_replace('/\d/', '', Tools::substr($customer->lastname, 0, 32));
        $new_address->postcode = $socolissimo_deliverypoint_info->codePostal;
        $new_address->city = str_replace('\'', ' ', $socolissimo_deliverypoint_info->localite);
        $new_address->id_country = $iso_code;
        $new_address->company = 'Livraison en Point de Retrait';
        $new_address->alias = 'Col-Point de retrait-'.date('d-m-Y');
        $new_address->address1 = $socolissimo_deliverypoint_info->adresse1;
        ((isset($socolissimo_deliverypoint_info->adresse2)) ? $new_address->address2 = $socolissimo_deliverypoint_info->adresse2 : $new_address->address2 = '');
        $this->context->cart->id_carrier = (int)Configuration::getGlobalValue('COLISSIMO_CARRIER_ID');
        $new_address->add();
        $new_address->deleted = 1;
        $new_address->save();
        return (int)$new_address->id;
    }

    public function hookAdminOrder($params)
    {
        require_once _PS_MODULE_DIR_.'colissimo_simplicite/classes/SCFields.php';

        $delivery_mode = array(
            'DOM' => 'Livraison à domicile',
            'BPR' => 'Livraison en Bureau de Poste',
            'A2P' => 'Livraison Commerce de proximité',
            'MRL' => 'Livraison Commerce de proximité',
            'CMT' => 'Livraison Commerce',
            'CIT' => 'Livraison en Cityssimo',
            'ACP' => 'Agence ColiPoste',
            'CDI' => 'Centre de distribution',
            'BDP' => 'Bureau de poste Belge',
            'RDV' => 'Livraison sur Rendez-vous');

        $order = new Order($params['id_order']);
        $address_delivery = new Address((int)$order->id_address_delivery, (int)$params['cookie']->id_lang);

        $so_carrier = new Carrier((int)Configuration::get('COLISSIMO_CARRIER_ID'));
        $order_carrier = new Carrier((int)$order->id_carrier);

        $id_colissimo_delivery_info = ColissimoDeliveryPoint::alreadyExists((int)$order->id_cart, (int)$order->id_customer);
        if ((int)$id_colissimo_delivery_info) {
            $delivery_infos = $this->getDeliveryInfosPointderetrait((int)$order->id_cart, (int)$order->id_customer);
            $sql = Db::getInstance()->getRow('SELECT * FROM '._DB_PREFIX_.'country c
                                          LEFT JOIN '._DB_PREFIX_.'country_lang cl ON cl.id_lang = '.(int)$params['cookie']->id_lang.'
                                          AND cl.id_country = c.id_country WHERE iso_code = "'.pSQL($delivery_infos['codePays']).'"');
            $name_country = $sql['name'];
            $is_home = false;
            $is_free_card = true;
        } else {
            $is_free_card = false;
            $id_colissimo_delivery_info = ColissimoDeliveryInfo::getDeliveryInfoExist((int)$order->id_cart, (int)$order->id_customer);
            if ((int)$id_colissimo_delivery_info) {
                $delivery_infos = new ColissimoDeliveryInfo((int)$id_colissimo_delivery_info);

                $sql = Db::getInstance()->getRow('SELECT * FROM '._DB_PREFIX_.'country c
										  LEFT JOIN '._DB_PREFIX_.'country_lang cl ON cl.id_lang = '.(int)$params['cookie']->id_lang.'
										  AND cl.id_country = c.id_country WHERE iso_code = "'.pSQL($delivery_infos->cecountry).'"');
                $name_country = $sql['name'];
                if (((int)$order_carrier->id_reference == (int)$so_carrier->id_reference) && $delivery_infos->id) {
                    $sc_fields = new SCFields($delivery_infos->delivery_mode);

                    switch ($sc_fields->delivery_mode) {
                        case SCFields::HOME_DELIVERY:
                            $is_home = true;
                            break;
                        case SCFields::RELAY_POINT:
                            $is_home = false;
                            break;
                    }
                }
            }
        }
        if ((int)$id_colissimo_delivery_info) {
            $this->context->smarty->assign(array(
                'path_img' => $this->_path.'logo.gif',
                'delivery_infos' => $delivery_infos,
                'address_delivery' => $address_delivery,
                'is_home' => $is_home,
                'name_country' => $name_country,
                'delivery_mode' => $delivery_mode,
                'is_free_card' => $is_free_card
            ));
            return $this->display(__FILE__, 'views/templates/hook/admin_order.tpl');
        }
    }

    public function hookActionCarrierUpdate($params)
    {
        if (Shop::isFeatureActive()) {
            Shop::setContext(Shop::CONTEXT_ALL);
        }

        if ((int)$params['id_carrier'] == (int)Configuration::get('COLISSIMO_CARRIER_ID')) {
            Configuration::updateValue('COLISSIMO_CARRIER_ID', (int)$params['carrier']->id);
        }
    }

    public function hookDisplayCarrierExtraContent($params)
    {
        $have_selected_point = false;
        $colissimo_delivery_info = false;
        $carrier_so = new Carrier((int)Configuration::get('COLISSIMO_CARRIER_ID'));

        if (!isset($carrier_so) || !$carrier_so->active) {
            return '';
        }
        $id_carrier = (int)$carrier_so->id;
        $address_delivery = new Address((int)$params['cart']->id_address_delivery);

        $country = new Country((int)$address_delivery->id_country);
        $carriers = Carrier::getCarriers(
            $this->context->language->id,
            true,
            false,
            false,
            null,
            (defined('ALL_CARRIERS') ? ALL_CARRIERS : Carrier::ALL_CARRIERS)
        );

        // bug fix for cart rule with restriction
        CartRule::autoAddToCart($this->context);

        // For now works only with single shipping !
        if (method_exists($params['cart'], 'carrierIsSelected')) {
            if ($params['cart']->carrierIsSelected((int)$carrier_so->id, $address_delivery->id)) {
                $id_carrier = (int)$carrier_so->id;
            }
        }

        $customer = new Customer($address_delivery->id_customer);

        $gender = array(
            '1' => 'MR',
            '2' => 'MME',
            '3' => 'MLE');

        if (in_array((int)$customer->id_gender, array(
                1,
                2))) {
            $cecivility = $gender[(int)$customer->id_gender];
        } else {
            $cecivility = 'MR';
        }

        $tax_rate = Tax::getCarrierTaxRate($id_carrier, isset($params['cart']->id_address_delivery) ? $params['cart']->id_address_delivery : null);
        $std_cost_with_taxes_float = (float)$this->initial_cost * (1 + ($tax_rate / 100));
        $std_cost_with_taxes_str = number_format($std_cost_with_taxes_float, 2, ',', ' ');

        $seller_cost_with_taxes_float = 0;
        $seller_cost_with_taxes_str = '';
        if (Configuration::get('COLISSIMO_COST_SELLER')) {
            if (Configuration::get('COLISSIMO_COST_IMPACT')) {
                $seller_cost_with_taxes_float = (float)Configuration::get('COLISSIMO_SELLER_AMOUNT') + $std_cost_with_taxes_float;
            } else {
                $seller_cost_with_taxes_float = $std_cost_with_taxes_float - (float)Configuration::get('COLISSIMO_SELLER_AMOUNT');
                if ($seller_cost_with_taxes_float < 0) {
                    $seller_cost_with_taxes_float = 0;
                }
            }
            $seller_cost_with_taxes_str = number_format($seller_cost_with_taxes_float, 2, ',', ' ');
        }

        $free_shipping = false;

        $rules = $params['cart']->getCartRules();
        if (!empty($rules)) {
            foreach ($rules as $rule) {
                if ($rule['free_shipping'] && !$rule['carrier_restriction']) {
                    $free_shipping = true;
                    break;
                }
            }
            if (!$free_shipping) {
                $key_search = $id_carrier.',';
                $deliveries_list = $params['cart']->getDeliveryOptionList();
                foreach ($deliveries_list as $deliveries) {
                    foreach ($deliveries as $key => $elt) {
                        if ($key == $key_search) {
                            $free_shipping = $elt['is_free'];
                        }
                    }
                }
            }
        } else {
            // for cart rule with restriction
            $key_search = $id_carrier.',';
            $deliveries_list = $params['cart']->getDeliveryOptionList();
            foreach ($deliveries_list as $deliveries) {
                foreach ($deliveries as $key => $elt) {
                    if ($key == $key_search) {
                        $free_shipping = $elt['is_free'];
                    }
                }
            }
        }

        // town fix
        $town = str_replace('\'', ' ', Tools::substr($address_delivery->city, 0, 32));
        $wsUrl = Configuration::get('COLISSIMO_WS_URL');
        if (Configuration::get('COLISSIMO_USE_POINTDERETRAIT')) {
            if ((int)ColissimoDeliveryPoint::alreadyExists($this->context->cart->id, $this->context->customer->id)) {
                $have_selected_point = true;
                $colissimo_delivery_info = new ColissimoDeliveryPoint((int)ColissimoDeliveryPoint::alreadyExists($this->context->cart->id, $this->context->customer->id));
            } else {
                $colissimo_delivery_info = new ColissimoDeliveryPoint();
            }
            $token = Configuration::get('COLISSIMO_TOKEN_POINTDERETRAIT');
            $token_update_date = Configuration::get('COLISSIMO_PDR_TOKEN_HOUR');
            $now = time();
            $diff = abs($now - (strtotime($token_update_date)));
            if ($token == null || $diff >= 899) { /* conrespond à 15 minutes */
                Configuration::updateGlobalValue('COLISSIMO_TOKEN_POINTDERETRAIT', $this->getTokenPointDeRetrait());
                Configuration::updateGlobalValue('COLISSIMO_PDR_TOKEN_HOUR', date('Y-m-d H:i:s'));
                $token = Configuration::get('COLISSIMO_TOKEN_POINTDERETRAIT');
            } else {
                $token = Configuration::get('COLISSIMO_TOKEN_POINTDERETRAIT');
            }


            $inputs = array(
                'ceCountry' => $this->replaceAccentedChars($town),
                'ceLang' => 'FR',
                'dyPreparationTime' => (int)Configuration::Get('COLISSIMO_PREPARATION_TIME'),
                'ceAddress' => $this->replaceAccentedChars(Tools::substr($address_delivery->address1, 0, 38)),
                'ceZipCode' => $this->replaceAccentedChars($address_delivery->postcode),
                'ceTown' => $this->replaceAccentedChars($town),
                'dyWeight' => (float)$params['cart']->getTotalWeight() * 1000,
                'cePays' => $country->iso_code,
                'token' => $token,
                'wsUrl' => $wsUrl,
            );
        } else {
            // Keep this fields order (see doc.)
            $inputs = array(
                'pudoFOId' => Configuration::get('COLISSIMO_ID'),
                'ceName' => $this->replaceAccentedChars(Tools::substr($address_delivery->lastname, 0, 34)),
                'dyPreparationTime' => (int)Configuration::Get('COLISSIMO_PREPARATION_TIME'),
                'dyForwardingCharges' => $std_cost_with_taxes_str,
                'dyForwardingChargesCMT' => $seller_cost_with_taxes_str,
                'trClientNumber' => (int)$address_delivery->id_customer,
                'orderId' => $this->formatOrderId((int)$address_delivery->id),
                'numVersion' => $this->api_num_version,
                'ceCivility' => $cecivility,
                'ceFirstName' => $this->replaceAccentedChars(Tools::substr($address_delivery->firstname, 0, 29)),
                'ceCompanyName' => $this->replaceAccentedChars(Tools::substr($address_delivery->company, 0, 38)),
                'ceAdress3' => $this->replaceAccentedChars(Tools::substr($address_delivery->address1, 0, 38)),
                'ceAdress4' => $this->replaceAccentedChars(Tools::substr($address_delivery->address2, 0, 38)),
                'ceZipCode' => $this->replaceAccentedChars($address_delivery->postcode),
                'ceTown' => $this->replaceAccentedChars($town),
                'ceEmail' => $this->replaceAccentedChars($params['cookie']->email),
                'cePhoneNumber' => $this->replaceAccentedChars(
                    str_replace(
                        array(
                        ' ',
                        '.',
                        '-',
                        ',',
                        ';',
                        '/',
                        '\\',
                        '(',
                        ')'
                        ),
                        '',
                        $address_delivery->phone_mobile
                    )
                ),
                'dyWeight' => (float)$params['cart']->getTotalWeight() * 1000,
                'trParamPlus' => $carrier_so->id,
                'trReturnUrlKo' => htmlentities($this->url, ENT_NOQUOTES, 'UTF-8'),
                'trReturnUrlOk' => htmlentities($this->url, ENT_NOQUOTES, 'UTF-8'),
                'CHARSET' => 'UTF-8',
                'cePays' => $country->iso_code,
                'trInter' => 1,
                'ceLang' => 'FR'
            );
        }
        if (isset($inputs['dyForwardingChargesCMT']) && !$inputs['dyForwardingChargesCMT'] && !Configuration::get('COLISSIMO_COST_SELLER')) {
            unset($inputs['dyForwardingChargesCMT']);
        }

        // set params for Api 3.0 if needed
        $inputs = $this->setInputParams($inputs);

        // generate key for API
        $inputs['signature'] = $this->generateKey($inputs);

        // calculate lowest cost
        $from_cost = $std_cost_with_taxes_str;
        if (($seller_cost_with_taxes_float < $std_cost_with_taxes_float ) && Configuration::get('COLISSIMO_COST_SELLER')) {
            $from_cost = $seller_cost_with_taxes_str;
        }
        $rewrite_active = true;
        if (!Configuration::get('PS_REWRITING_SETTINGS')) {
            $rewrite_active = false;
        }

        $link = new Link();
        if (Configuration::get('COLISSIMO_USE_POINTDERETRAIT') == '1') {
            $module_link = $link->getModuleLink('colissimo_simplicite', 'redirectdeliverypoint', array(), true);
        } else {
            $module_link = $link->getModuleLink('colissimo_simplicite', 'redirect', array(), true);
        }
        $module_link_mobile = $link->getModuleLink('colissimo_simplicite', 'redirectmobile', array(), true);

        // automatic settings api protocol for ssl
        $protocol = 'http://';
        if (Configuration::get('PS_SSL_ENABLED')) {
            $protocol = 'https://';
        }

        $from_mention = $this->l('From Cost');
        $initial_cost = $from_cost.$this->l(' €');
        $tax_mention = $this->l(' TTC');
        if ($free_shipping) {
            $from_mention = '';
            $initial_cost = $this->l('Free (Will be apply after address selection)');
            $tax_mention = '';
        }

        $on_mobile_device = false;

        if ($this->isMobileDevice()) {
            $on_mobile_device = true;
        }
        $base_url = $protocol.Tools::getShopDomainSsl().__PS_BASE_URI__;
        $this->context->smarty->assign(array(
            'select_label' => $this->l('Select delivery mode'),
            'edit_label' => $this->l('Edit delivery mode'),
            'token' => sha1('colissimo'._COOKIE_KEY_.Context::getContext()->cookie->id_cart),
            'urlSo' => $protocol.Configuration::get('COLISSIMO_URL').'?trReturnUrlKo='.htmlentities($this->url, ENT_NOQUOTES, 'UTF-8'),
            'urlSoMobile' => $protocol.Configuration::get('COLISSIMO_URL_MOBILE').'?trReturnUrlKo='.htmlentities($this->url, ENT_NOQUOTES, 'UTF-8'),
            'id_carrier' => $id_carrier,
            'inputs' => $inputs,
            'initialCost_label' => $from_mention,
            'initialCost' => $initial_cost, // to change label for price in tpl
            'taxMention' => $tax_mention, // to change label for price in tpl
            'finishProcess' => $this->l('To choose SoColissimo, click on a delivery method'),
            'rewrite_active' => $rewrite_active,
            'link_socolissimo' => $module_link,
            'link_socolissimo_mobile' => $module_link_mobile,
            'module_link' => $module_link,
            'on_mobile_device' => $on_mobile_device,
            'link_to_img' => $this->link_to_img,
            'wsUrl' => $protocol.$wsUrl,
            'baseUrl' => $base_url,
            'msg_order_carrier_colissimo' => $this->l('Before proceding, you must select relay point first.'),
            'have_selected_point' => (int)$have_selected_point,
            'relay_info' => $colissimo_delivery_info
        ));
        if (Configuration::get('COLISSIMO_USE_POINTDERETRAIT')) {
            return $this->display(__FILE__, 'colissimo_delivery_point.tpl');
        }
        return $this->display(__FILE__, 'extra_content.tpl');
    }

    /**
     * Validate SIRET Code Taken from prestashop core for compatibility 1.4 reason
     * @static
     * @param $siret SIRET Code
     * @return boolean Return true if is valid
     */
    public function isSiret($siret)
    {
        if (Tools::strlen($siret) != 14) {
            return false;
        }
        $sum = 0;
        for ($i = 0; $i != 14; $i++) {
            $tmp = ((($i + 1) % 2) + 1) * (int)$siret[$i];
            if ($tmp >= 10) {
                $tmp -= 9;
            }
            $sum += $tmp;
        }
        return ($sum % 10 === 0);
    }

    public function getApiPrice($id_cart)
    {
        if ((int)$id_cart) {
            return Db::getInstance()->getValue('SELECT dyforwardingcharges
            FROM '._DB_PREFIX_.'colissimo_delivery_info
            WHERE id_cart = '.(int)$id_cart);
        }
        return false;
    }

    public function checkZone($id_carrier)
    {
        return (bool)Db::getInstance()->getRow('SELECT * FROM '._DB_PREFIX_.'carrier_zone WHERE id_carrier = '.(int)$id_carrier);
    }

    public function checkGroup($id_carrier)
    {
        return (bool)Db::getInstance()->getRow('SELECT * FROM '._DB_PREFIX_.'carrier_group WHERE id_carrier = '.(int)$id_carrier);
    }

    public function checkRange($id_carrier)
    {
        $sql = '';
        $carrier = new Carrier($id_carrier);
        if ($carrier->shipping_method) {
            switch ($carrier->shipping_method) {
                case '2':
                    $sql = 'SELECT * FROM '._DB_PREFIX_.'range_price WHERE id_carrier = '.(int)$id_carrier;
                    break;
                case '1':
                    $sql = 'SELECT * FROM '._DB_PREFIX_.'range_weight WHERE id_carrier = '.(int)$id_carrier;
                    break;
            }
        }
        if (!$sql) {
            switch (Configuration::get('PS_SHIPPING_METHOD')) {
                case '0':
                    $sql = 'SELECT * FROM '._DB_PREFIX_.'range_price WHERE id_carrier = '.(int)$id_carrier;
                    break;
                case '1':
                    $sql = 'SELECT * FROM '._DB_PREFIX_.'range_weight WHERE id_carrier = '.(int)$id_carrier;
                    break;
            }
        }
        return (bool)Db::getInstance()->getRow($sql);
    }

    public function checkDelivery($id_carrier)
    {
        return (bool)Db::getInstance()->getRow('SELECT * FROM '._DB_PREFIX_.'delivery WHERE id_carrier = '.(int)$id_carrier);
    }

    public function reallocationCarrier($id_socolissimo)
    {
        // carrier must be module carrier
        Db::getInstance()->execute('UPDATE '._DB_PREFIX_.'carrier SET
            shipping_handling = 0,
            is_module = 1,
            shipping_external = 1,
            need_range = 1,
            external_module_name = "colissimo_simplicite"
            WHERE  id_carrier = '.(int)$id_socolissimo);

        // old carrier no longer linked with socolissimo
        Db::getInstance()->execute('UPDATE '._DB_PREFIX_.'carrier SET
            is_module = 0,
            external_module_name = ""
            WHERE  id_carrier NOT IN ('.(int)Configuration::get('COLISSIMO_CARRIER_ID').')
            AND external_module_name = "colissimo_simplicite"
        ');
    }

    /**
     * Generate good order id format.
     *
     * @param $id
     * @return string
     */
    public function formatOrderId($id)
    {
        $str_len = Tools::strlen($id);
        while ($str_len < 5) {
            $id = '0'.$id;
            $str_len = Tools::strlen($id);
        }
        return $id;
    }

    /**
     * @param $str
     * @return mixed
     */
    public function replaceAccentedChars($str)
    {
        $str = preg_replace(
            array(
            /* Lowercase */
            '/[\x{0105}\x{00E0}\x{00E1}\x{00E2}\x{00E3}\x{00E4}\x{00E5}]/u',
            '/[\x{00E7}\x{010D}\x{0107}]/u',
            '/[\x{010F}]/u',
            '/[\x{00E8}\x{00E9}\x{00EA}\x{00EB}\x{011B}\x{0119}]/u',
            '/[\x{00EC}\x{00ED}\x{00EE}\x{00EF}]/u',
            '/[\x{0142}\x{013E}\x{013A}]/u',
            '/[\x{00F1}\x{0148}]/u',
            '/[\x{00F2}\x{00F3}\x{00F4}\x{00F5}\x{00F6}\x{00F8}]/u',
            '/[\x{0159}\x{0155}]/u',
            '/[\x{015B}\x{0161}]/u',
            '/[\x{00DF}]/u',
            '/[\x{0165}]/u',
            '/[\x{00F9}\x{00FA}\x{00FB}\x{00FC}\x{016F}]/u',
            '/[\x{00FD}\x{00FF}]/u',
            '/[\x{017C}\x{017A}\x{017E}]/u',
            '/[\x{00E6}]/u',
            '/[\x{0153}]/u',
            /* Uppercase */
            '/[\x{0104}\x{00C0}\x{00C1}\x{00C2}\x{00C3}\x{00C4}\x{00C5}]/u',
            '/[\x{00C7}\x{010C}\x{0106}]/u',
            '/[\x{010E}]/u',
            '/[\x{00C8}\x{00C9}\x{00CA}\x{00CB}\x{011A}\x{0118}]/u',
            '/[\x{0141}\x{013D}\x{0139}]/u',
            '/[\x{00D1}\x{0147}]/u',
            '/[\x{00D3}]/u',
            '/[\x{0158}\x{0154}]/u',
            '/[\x{015A}\x{0160}]/u',
            '/[\x{0164}]/u',
            '/[\x{00D9}\x{00DA}\x{00DB}\x{00DC}\x{016E}]/u',
            '/[\x{017B}\x{0179}\x{017D}]/u',
            '/[\x{00C6}]/u',
            '/[\x{0152}]/u',
            ),
            array(
            'a',
            'c',
            'd',
            'e',
            'i',
            'l',
            'n',
            'o',
            'r',
            's',
            'ss',
            't',
            'u',
            'y',
            'z',
            'ae',
            'oe',
            'A',
            'C',
            'D',
            'E',
            'L',
            'N',
            'O',
            'R',
            'S',
            'T',
            'U',
            'Z',
            'AE',
            'OE'
            ),
            $str
        );
        $array_unauthorised_api = array(
            ';',
            '€',
            '~',
            '#',
            '{',
            '(',
            '[',
            '|',
            '\\',
            '^',
            ')',
            ']',
            '=',
            '}',
            '$',
            '¤',
            '£',
            '%',
            'μ',
            '*',
            '§',
            '!',
            '°',
            '²',
            '"',
        );
        foreach ($array_unauthorised_api as $key => $value) {
            $str = str_replace($value, '', $str);
        }
        $str = str_replace('\'', ' ', $str);
        $str = preg_replace('/\s+/', ' ', $str);

        return $str;
    }

    /**
     * @param array
     * @return array
     */
    public function setInputParams($inputs)
    {
        $get_mobile_device = Context::getContext()->getMobileDevice();

        // set api params for 4.0 and mobile
        if ($get_mobile_device || $this->isIpad() || $this->isMobile()) {
            unset($inputs['CHARSET']);
            $inputs['numVersion'] = '4.0';
        }
        return $inputs;
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

    public function upper($str_in)
    {
        return Tools::strtoupper(str_replace('-', ' ', Tools::link_rewrite($str_in)));
    }

    public function lower($str_in)
    {
        return Tools::strtolower(str_replace('-', ' ', Tools::link_rewrite($str_in)));
    }

    public function formatName($name)
    {
        return preg_replace('/[0-9!<>,;?=+()@#"°{}_$%:]/', '', Tools::stripslashes($name));
    }

    public function isSameAddress($id_address, $id_cart, $id_customer)
    {
        $id_colissimo_delivery_info = ColissimoDeliveryInfo::getDeliveryInfoExist((int)$id_cart, (int)$id_customer);
        if (!$id_colissimo_delivery_info) {
            return $id_address;
        }
        $colissimo_delivery_info = new ColissimoDeliveryInfo((int)$id_colissimo_delivery_info);
        $ps_address = new Address((int)$id_address);
        $new_address = new Address();
        $sql = Db::getInstance()->getRow('SELECT c.id_country, cl.name FROM '._DB_PREFIX_.'country c
										  LEFT JOIN '._DB_PREFIX_.'country_lang cl ON cl.id_lang = '.(int)$this->context->language->id.'
										  AND cl.id_country = c.id_country WHERE iso_code = "'.pSQL($colissimo_delivery_info->cecountry).'"');

        $iso_code = $sql['id_country'];

        if ($this->upper($ps_address->lastname) != $this->upper($colissimo_delivery_info->prname) || $ps_address->id_country != $iso_code || $this->upper($ps_address->firstname) != $this->upper($colissimo_delivery_info->prfirstname)
            || $this->upper($ps_address->address1) != $this->upper($colissimo_delivery_info->pradress3) || $this->upper($ps_address->address2) != $this->upper($colissimo_delivery_info->pradress2) || $this->upper($ps_address->postcode)
            != $this->upper($colissimo_delivery_info->przipcode) || $this->upper($ps_address->city) != $this->upper($colissimo_delivery_info->prtown) || str_replace(array(
                ' ',
                '.',
                '-',
                ',',
                ';',
                '+',
                '/',
                '\\',
                '+',
                '(',
                ')'), '', $ps_address->phone_mobile) != $colissimo_delivery_info->cephonenumber) {
            $new_address->id_customer = (int)$id_customer;
            $firstname_company = preg_replace('/\d/', '', Tools::substr($colissimo_delivery_info->prfirstname, 0, 31));
            $lastname_company = preg_replace('/\d/', '', Tools::substr($colissimo_delivery_info->prname, 0, 32));
            $firstname = preg_replace('/\d/', '', Tools::substr($colissimo_delivery_info->cefirstname, 0, 32));
            $lastname = preg_replace('/\d/', '', Tools::substr($colissimo_delivery_info->cename, 0, 32));
            $new_address->lastname = trim($this->formatName($lastname));
            $new_address->firstname = trim($this->formatName($firstname));
            $new_address->postcode = $colissimo_delivery_info->przipcode;
            $new_address->city = str_replace('\'', ' ', $colissimo_delivery_info->prtown);
            $new_address->id_country = $iso_code;
            $new_address->alias = 'Colissimo - '.date('d-m-Y');
            $new_address->phone_mobile = $colissimo_delivery_info->cephonenumber;
            $new_address->company = trim($colissimo_delivery_info->cecompanyname);

            if (!in_array($colissimo_delivery_info->delivery_mode, array(
                    'DOM',
                    'RDV'))) {
                /**$firstname_company_formatted = trim($this->formatName($firstname_company));
                $lastname_company_formatted = trim($this->formatName($lastname_company));
                $new_address->company = $firstname_company_formatted.' '.$lastname_company_formatted;**/
                $new_address->active = 0;
                $new_address->deleted = 1;
                $new_address->address1 = $colissimo_delivery_info->pradress1;
                $new_address->address2 = $colissimo_delivery_info->pradress2;
                $new_address->add();
                $new_address->deleted = 1;
                $new_address->save();
            } else {
                $new_address->address1 = $colissimo_delivery_info->pradress3;
                ((isset($colissimo_delivery_info->pradress2)) ? $new_address->address2 = $colissimo_delivery_info->pradress2 : $new_address->address2 = '');
                ((isset($colissimo_delivery_info->pradress1)) ? $new_address->other .= $colissimo_delivery_info->pradress1 : $new_address->other = '');
                ((isset($colissimo_delivery_info->pradress4)) ? $new_address->other .= ' | '.$colissimo_delivery_info->pradress4 : $new_address->other = '');
                $new_address->postcode = $colissimo_delivery_info->przipcode;
                $new_address->city = str_replace('\'', ' ', $colissimo_delivery_info->prtown);
                $new_address->id_country = $iso_code;
                $new_address->alias = 'Colissimo - '.date('d-m-Y');
                $new_address->add();
                $new_address->active = 0;
                $new_address->deleted = 1;
                $new_address->save();
            }
            return (int)$new_address->id;
        }
        return (int)$ps_address->id;
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
        } else {
            return false;
        }
    }

    /**
     * Generate the signed key
     *
     * @static
     * @param $params
     * @return string
     */
    public function generateKey($params)
    {
        $str = '';

        foreach ($params as $key => $value) {
            if (!in_array(Tools::strtoupper($key), array(
                    'SIGNATURE'))) {
                $str .= utf8_decode($value);
            }
        }

        return sha1($str.Tools::strtolower(Configuration::get('COLISSIMO_KEY')));
    }

    public function getTokenPointDeRetrait()
    {

        $url = Configuration::get('COLISSIMO_URL_POINTDERETRAIT');
        $login = Configuration::get('COLISSIMO_LOGIN');
        $password = Configuration::get('COLISSIMO_PASSWORD');
        $params = array(
            'login' => $login,
            'password' => $password
        );
        $autorisation = 'Authorization: Basic';
        $content_type = 'Content-Type: application/x-www-form-urlencoded; charset=utf-8';


        $curl = curl_init($url);
        curl_setopt_array(
            $curl,
            array(
            CURLOPT_HTTPHEADER => array(
                $content_type,
                'Accept: application/json',
                $autorisation
            ),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HEADER => false,
            CURLOPT_POSTFIELDS => http_build_query($params),
            CURLOPT_SSL_VERIFYPEER => false
            )
        );
        $curl_response = curl_exec($curl);
        if ($curl_response) {
            $json = json_decode($curl_response);
            if ($json instanceof stdClass && property_exists($json, 'token')) {
                return $json->token;
            }
        }
        return false;
    }
}
