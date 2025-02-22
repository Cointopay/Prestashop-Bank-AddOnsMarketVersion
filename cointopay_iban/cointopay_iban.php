<?php
/**
 * 2010-2025 PrestaShop
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
 *  @copyright  2010-2025 PrestaShop SA
 *  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

require_once _PS_MODULE_DIR_ . '/cointopay_iban/vendor/cointopay_iban/init.php';
require_once _PS_MODULE_DIR_ . '/cointopay_iban/vendor/version.php';

class Cointopay_Iban extends PaymentModule
{
    public $merchant_id;
    public $security_code;
    public $crypto_currency;
    private $html = '';
    private $postErrors = [];
    public $fields_form = [];
    public $displayName;

    public function __construct()
    {
        $this->module_key = '';
        $this->name = 'cointopay_iban';
        $this->tab = 'payments_gateways';
        $this->version = '1.0.0';
        $this->ps_versions_compliancy = ['min' => '1.7', 'max' => _PS_VERSION_];
        $this->author = 'Cointopay';
        $this->currencies = true;
        $this->currencies_mode = 'checkbox';

        $this->bootstrap = true;
        $config = Configuration::getMultiple(['COINTOPAY_IBAN_MERCHANT_ID', 'COINTOPAY_IBAN_SECURITY_CODE', 'COINTOPAY_IBAN_CRYPTO_CURRENCY', 'COINTOPAY_IBAN_DISPLAY_NAME']);

        if (!empty($config['COINTOPAY_IBAN_MERCHANT_ID'])) {
            $this->merchant_id = $config['COINTOPAY_IBAN_MERCHANT_ID'];
        }
        if (!empty($config['COINTOPAY_IBAN_SECURITY_CODE'])) {
            $this->security_code = $config['COINTOPAY_IBAN_SECURITY_CODE'];
        }
        if (!empty($config['COINTOPAY_IBAN_CRYPTO_CURRENCY'])) {
            $this->crypto_currency = $config['COINTOPAY_IBAN_CRYPTO_CURRENCY'];
        }

        parent::__construct();

        $this->displayName = 'Cointopay Pay via Bank';
        $this->description = $this->l('Accept payments on your Prestashop store with Cointopay');
        $this->confirmUninstall = $this->l('Are you sure you want to delete your details?');

        if (!isset($this->merhcant_id) || !isset($this->security_code)) {
            $this->warning = $this->l('API Access details must be configured in order to use this module correctly.');
        }

        if (!count(Currency::checkPaymentCurrencies($this->id))) {
            $this->warning = $this->l('No currency has been set for this module.');
        }
    }

    public function install()
    {
        if (!function_exists('curl_version')) {
            $this->_errors[] = $this->l('This module requires cURL PHP extension in order to function normally.');
            return false;
        }

        $order_ctp_pending = new OrderState();
        $order_ctp_pending->module_name = $this->name;
        $order_ctp_pending->name = array_fill(0, 10, 'Waiting for cointopay_iban transaction');
        $order_ctp_pending->send_email = 0;
        $order_ctp_pending->invoice = 0;
        $order_ctp_pending->color = 'RoyalBlue';
        $order_ctp_pending->unremovable = false;
        $order_ctp_pending->logable = 0;

        $order_ctp_waiting = new OrderState();
        $order_ctp_waiting->module_name = $this->name;
        $order_ctp_waiting->name = array_fill(0, 10, 'Waiting for cointopay_iban confirmation');
        $order_ctp_waiting->send_email = 0;
        $order_ctp_waiting->invoice = 0;
        $order_ctp_waiting->color = 'RoyalBlue';
        $order_ctp_waiting->unremovable = false;
        $order_ctp_waiting->logable = 0;

        $order_processing = new OrderState();
        $order_processing->module_name = $this->name;
        $order_processing->name = array_fill(0, 10, 'Cointopay processing in progress');
        $order_processing->send_email = 0;
        $order_processing->invoice = 0;
        $order_processing->color = 'RoyalBlue';
        $order_processing->unremovable = false;
        $order_processing->logable = 0;

        $order_failed = new OrderState();
        $order_failed->module_name = $this->name;
        $order_failed->name = array_fill(0, 10, 'Cointopay payment failed');
        $order_failed->send_email = 0;
        $order_failed->invoice = 0;
        $order_failed->color = '#FF8C00';
        $order_failed->unremovable = false;
        $order_failed->logable = 0;

        $order_expired = new OrderState();
        $order_expired->module_name = $this->name;
        $order_expired->name = array_fill(0, 10, 'Cointopay payment expired');
        $order_expired->send_email = 0;
        $order_expired->invoice = 0;
        $order_expired->color = '#DC143C';
        $order_expired->unremovable = false;
        $order_expired->logable = 0;

        $order_invalid = new OrderState();
        $order_invalid->module_name = $this->name;
        $order_invalid->name = array_fill(0, 10, 'Cointopay invoice is invalid');
        $order_invalid->send_email = 0;
        $order_invalid->invoice = 0;
        $order_invalid->color = '#8f0621';
        $order_invalid->unremovable = false;
        $order_invalid->logable = 0;

        $order_not_enough = new OrderState();
        $order_not_enough->module_name = $this->name;
        $order_not_enough->name = array_fill(0, 10, 'Cointopay not enough payment');
        $order_not_enough->send_email = 0;
        $order_not_enough->invoice = 0;
        $order_not_enough->color = '#32CD32';
        $order_not_enough->unremovable = false;
        $order_not_enough->logable = 0;

        if ($order_ctp_pending->add()) {
            copy(
                _PS_ROOT_DIR_ . '/modules/cointopay_iban/views/img/logo.png',
                _PS_ROOT_DIR_ . '/img/os/' . (int) $order_ctp_pending->id . '.gif'
            );
        }

        if ($order_ctp_waiting->add()) {
            copy(
                _PS_ROOT_DIR_ . '/modules/cointopay_iban/views/img/logo.png',
                _PS_ROOT_DIR_ . '/img/os/' . (int) $order_ctp_waiting->id . '.gif'
            );
        }

        if ($order_processing->add()) {
            copy(
                _PS_ROOT_DIR_ . '/modules/cointopay_iban/views/img/logo.png',
                _PS_ROOT_DIR_ . '/img/os/' . (int) $order_processing->id . '.gif'
            );
        }

        if ($order_failed->add()) {
            copy(
                _PS_ROOT_DIR_ . '/modules/cointopay_iban/views/img/logo.png',
                _PS_ROOT_DIR_ . '/img/os/' . (int) $order_failed->id . '.gif'
            );
        }

        if ($order_expired->add()) {
            copy(
                _PS_ROOT_DIR_ . '/modules/cointopay_iban/views/img/logo.png',
                _PS_ROOT_DIR_ . '/img/os/' . (int) $order_expired->id . '.gif'
            );
        }

        if ($order_invalid->add()) {
            copy(
                _PS_ROOT_DIR_ . '/modules/cointopay_iban/views/img/logo.png',
                _PS_ROOT_DIR_ . '/img/os/' . (int) $order_invalid->id . '.gif'
            );
        }

        if ($order_not_enough->add()) {
            copy(
                _PS_ROOT_DIR_ . '/modules/cointopay_iban/views/img/logo.png',
                _PS_ROOT_DIR_ . '/img/os/' . (int) $order_not_enough->id . '.gif'
            );
        }

        Configuration::updateValue('COINTOPAY_IBAN_PROCESSING_IN_PROGRESS', $order_processing->id);
        Configuration::updateValue('COINTOPAY_IBAN_NOTENOUGH', $order_not_enough->id);
        Configuration::updateValue('COINTOPAY_IBAN_FAILED', $order_failed->id);
        Configuration::updateValue('COINTOPAY_IBAN_EXPIRED', $order_expired->id);
        Configuration::updateValue('COINTOPAY_IBAN_INVALID', $order_invalid->id);
        Configuration::updateValue('COINTOPAY_IBAN_PENDING', $order_ctp_pending->id);
        Configuration::updateValue('COINTOPAY_IBAN_WAITING', $order_ctp_waiting->id);

        if (
            !parent::install()
            || !$this->registerHook('ActionpaymentReturn')
            || !$this->registerHook('paymentOptions')
        ) {
            return false;
        }

        return true;
    }

    public function uninstall()
    {
        $order_state_processing = new OrderState(Configuration::get('COINTOPAY_IBAN_PROCESSING_IN_PROGRESS'));
        $order_not_enough = new OrderState(Configuration::get('COINTOPAY_IBAN_NOTENOUGH'));
        $order_state_failed = new OrderState(Configuration::get('COINTOPAY_IBAN_FAILED'));
        $order_state_expired = new OrderState(Configuration::get('COINTOPAY_IBAN_EXPIRED'));
        $order_state_invalid = new OrderState(Configuration::get('COINTOPAY_IBAN_INVALID'));
        $order_state_pending = new OrderState(Configuration::get('COINTOPAY_IBAN_PENDING'));
        $order_state_waiting = new OrderState(Configuration::get('COINTOPAY_IBAN_WAITING'));
        Configuration::deleteByName('COINTOPAY_IBAN_MERCHANT_ID');
        Configuration::deleteByName('COINTOPAY_IBAN_SECURITY_CODE');
        Configuration::deleteByName('COINTOPAY_IBAN_DISPLAY_NAME');
        Configuration::deleteByName('COINTOPAY_IBAN_CRYPTO_CURRENCY');
        $order_state_processing->delete();
        $order_not_enough->delete();
        $order_state_failed->delete();
        $order_state_expired->delete();
        $order_state_invalid->delete();
        $order_state_pending->delete();
        $order_state_waiting->delete();
        parent::uninstall();

        return true;
    }

    public function getContent()
    {
        if (Tools::isSubmit('btnSubmit')) {
            $this->postValidation();
            if (!count($this->postErrors)) {
                $this->postProcess();
            } else {
                foreach ($this->postErrors as $err) {
                    $this->html .= $this->displayError($err);
                }
            }
        } else {
            $this->html .= '<br />';
        }

        $renderForm = $this->renderForm();
        $this->html .= $this->displayCointopayInformation($renderForm);

        return $this->html;
    }

    private function postValidation()
    {
        if (Tools::isSubmit('btnSubmit')) {
            if (!Tools::getValue('COINTOPAY_IBAN_MERCHANT_ID')) {
                $this->postErrors[] = $this->l('Merchant id is required.');
            }

            if (!Tools::getValue('COINTOPAY_IBAN_SECURITY_CODE')) {
                $this->postErrors[] = $this->l('Security Code is required.');
            }

            if (!Tools::getValue('COINTOPAY_IBAN_CRYPTO_CURRENCY')) {
                $this->postErrors[] = $this->l('Checkout Currency is required.');
            }

            if (empty($this->postErrors)) {
                $ctpConfig = [
                    'merchant_id' => Tools::getValue('COINTOPAY_IBAN_MERCHANT_ID'),
                    'security_code' => Tools::getValue('COINTOPAY_IBAN_SECURITY_CODE'),
                    'selected_currency' => Tools::getValue('COINTOPAY_IBAN_CRYPTO_CURRENCY'),
                    'user_agent' => 'Cointopay - Prestashop v' . _PS_VERSION_ . ' Extension v' . COINTOPAY_IBAN_PRESTASHOP_EXTENSION_VERSION,
                ];

                \cointopay_iban\Cointopay_Iban::config($ctpConfig);
                $merchant = \cointopay_iban\Cointopay_Iban::verifyMerchant();

                if ($merchant !== true) {
                    $this->postErrors[] = $this->l($merchant);
                }
            }
        }
    }

    private function postProcess()
    {
        if (Tools::isSubmit('btnSubmit')) {
            Configuration::updateValue('COINTOPAY_IBAN_DISPLAY_NAME', Tools::getValue('COINTOPAY_IBAN_DISPLAY_NAME'));
            Configuration::updateValue('COINTOPAY_IBAN_MERCHANT_ID', Tools::getValue('COINTOPAY_IBAN_MERCHANT_ID'));
            Configuration::updateValue('COINTOPAY_IBAN_SECURITY_CODE', Tools::getValue('COINTOPAY_IBAN_SECURITY_CODE'));
            Configuration::updateValue('COINTOPAY_IBAN_CRYPTO_CURRENCY', Tools::getValue('COINTOPAY_IBAN_CRYPTO_CURRENCY'));
        }

        $this->html .= $this->displayConfirmation($this->l('Settings updated'));
    }

    public function renderForm()
    {
        $options = [
            [
                'id_option' => 1,
                'name' => 'Select default checkout currency',
            ],
        ];
        $fields_form = [
            'form' => [
                'legend' => [
                    'title' => $this->l('Fiat Payment options with Cointopay.com'),
                    'icon' => 'icon-dollar',
                ],
                'input' => [
                    [
                        'type' => 'text',
                        'label' => $this->l('Display Name'),
                        'name' => 'COINTOPAY_IBAN_DISPLAY_NAME',
                        'required' => true,
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('Merchant ID'),
                        'name' => 'COINTOPAY_IBAN_MERCHANT_ID',
                        'desc' => $this->l('Your ID (created on Cointopay)'),
                        'required' => true,
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('Security Code'),
                        'name' => 'COINTOPAY_IBAN_SECURITY_CODE',
                        'desc' => $this->l('Your Security Code (created on Cointopay)'),
                        'required' => true,
                    ],
                    [
                        'type' => 'select',
                        'label' => $this->l('Select default checkout currency'),
                        'name' => 'COINTOPAY_IBAN_CRYPTO_CURRENCY',
                        'id' => 'crypto_currency',
                        'default_value' => (int) Tools::getValue('COINTOPAY_IBAN_CRYPTO_CURRENCY'),
                        'required' => true,
                        'options' => [
                            'query' => $options,
                            'id' => 'id_option',
                            'name' => 'name',
                        ],
                    ],
                ],
                'submit' => [
                    'title' => $this->l('Save'),
                ],
            ],
        ];

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $lang = new Language((int) Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = (Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0);
        $this->fields_form = [];
        $helper->id = (int) Tools::getValue('id_carrier');
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'btnSubmit';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false) . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = [
            'fields_value' => $this->getConfigFormValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        ];

        return $helper->generateForm([$fields_form]);
    }

    protected function getConfigFormValues()
    {
        $system_name = Configuration::get('COINTOPAY_IBAN_DISPLAY_NAME');
        $display_name = !empty($system_name) ? $system_name : 'Cointopay Pay via Bank';

        return ['COINTOPAY_IBAN_DISPLAY_NAME' => $display_name, 'COINTOPAY_IBAN_MERCHANT_ID' => Configuration::get('COINTOPAY_IBAN_MERCHANT_ID'), 'COINTOPAY_IBAN_SECURITY_CODE' => Configuration::get('COINTOPAY_IBAN_SECURITY_CODE'), 'COINTOPAY_IBAN_CRYPTO_CURRENCY' => Configuration::get('COINTOPAY_IBAN_CRYPTO_CURRENCY')];
    }

    private function displayCointopayInformation($renderForm)
    {
        $this->html .= $this->displayCointopay();
        // Create a link with the path

        $ctp_bank_coins_ajax_link = $this->context->link->getModuleLink($this->name, 'getcoins', [], true);
        // define js value to use in ajax url
        Media::addJsDef(['ctp_bank_coins_ajax_link' => $ctp_bank_coins_ajax_link]);

        $this->context->controller->addCSS($this->_path . '/views/css/tabs.css', 'all');
        $this->context->controller->addJS($this->_path . '/views/js/javascript.js', 'all');
        $this->context->controller->addJS($this->_path . '/views/js/cointopay.js', 'all');

        $this->context->smarty->assign('form', $renderForm);
        $this->context->smarty->assign('selected_currency', Configuration::get('COINTOPAY_IBAN_CRYPTO_CURRENCY'));

        return $this->display(__FILE__, 'information.tpl');
    }

    private function displayCointopay()
    {
        return $this->display(__FILE__, 'infos.tpl');
    }

    public function checkCurrency($cart)
    {
        $currency_order = new Currency($cart->id_currency);
        $currencies_module = $this->getCurrency($cart->id_currency);

        if (is_array($currencies_module)) {
            foreach ($currencies_module as $currency_module) {
                if ($currency_order->id == $currency_module['id_currency']) {
                    return true;
                }
            }
        }

        return false;
    }

    public function hookActionPaymentReturn($params)
    {
        if (!$this->active) {
            return;
        }
        $this->context->controller->addJS($this->_path . '/views/js/cointopay_custom.js', 'all');
        array_push($params, $_REQUEST);
        // Check if 'CustomerReferenceNr' exists in the request
        if (isset($_REQUEST['CustomerReferenceNr'])) {
            $this->smarty->assign('getparams', $_REQUEST);
            // Assign dynamic URLs to the template
            $this->context->smarty->assign([
                'ctpAjaxUrl' => $this->context->link->getModuleLink($this->name, 'cointopay_waiting', [], true),
                'ctpCallbackUrl' => $this->context->link->getModuleLink($this->name, 'callback', [], true),
            ]);
            // Return the success callback template using explicit template path
            return $this->context->smarty->fetch($this->getTemplatePath('views/templates/hook/ctp_success_callback.tpl'));
        }
    }

    public function hookPaymentOptions($params)
    {
        if (!$this->active) {
            return;
        }

        if (!$this->checkCurrency($params['cart'])) {
            return;
        }
        $newOption = new PrestaShop\PrestaShop\Core\Payment\PaymentOption();
        $newOption->setCallToActionText($this->displayName)
            ->setAction($this->context->link->getModuleLink($this->name, 'validation', [], true))
            ->setAdditionalInformation(
                $this->context->smarty->fetch('module:cointopay_iban/views/templates/hook/cointopay_intro.tpl')
            )
            ->setLogo(Media::getMediaPath(_PS_MODULE_DIR_ . $this->name . '/order-page.png'));

        return [$newOption];
    }
}
