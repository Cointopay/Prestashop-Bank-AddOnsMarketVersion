<?php
/**
 * 2007-2025 PrestaShop
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
 * @author PrestaShop SA <contact@prestashop.com>
 * @copyright  2007-2025 PrestaShop SA
 * @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */
if (!defined('_PS_VERSION_')) {
    exit;
}
require_once _PS_MODULE_DIR_ . '/cointopay_iban/vendor/cointopay_iban/init.php';
require_once _PS_MODULE_DIR_ . '/cointopay_iban/vendor/version.php';

class Cointopay_IbanValidationModuleFrontController extends ModuleFrontController
{
    /**
     * @see FrontController::postProcess()
     */
    public function postProcess()
    {
        $selected_currency = Tools::getValue('selected_currency');
        $cart = $this->context->cart;
        if ($cart->id_customer == 0 || $cart->id_address_delivery == 0 || $cart->id_address_invoice == 0 || !$this->module->active) {
            Tools::redirect('index.php?controller=order&step=1');
        }

        // Check that this payment option is still available in case the customer changed his address just before the end of the checkout process
        $authorized = false;
        foreach (Module::getPaymentModules() as $module) {
            if ($module['name'] == 'cointopay_iban') {
                $authorized = true;
                break;
            }
        }

        if (!$authorized) {
            exit($this->module->l('This payment method is not available.', 'validation'));
        }

        $customer = new Customer($cart->id_customer);

        if (!Validate::isLoadedObject($customer)) {
            Tools::redirect('index.php?controller=order&step=1');
        }

        $currency = $this->context->currency;
        $total = (float) $cart->getOrderTotal(true, Cart::BOTH);
        $this->module->validateOrder($cart->id, Configuration::get('COINTOPAY_IBAN_PENDING'), $total, $this->module->displayName, null, [], (int) $currency->id, false, $customer->secure_key);
        $link = new Link();
        $success_url = '';
        $success_url = $link->getPageLink('order-confirmation', null, null, [
            'id_cart' => $cart->id,
            'id_module' => $this->module->id,
            'key' => $customer->secure_key,
        ]);
        $description = [];
        foreach ($cart->getProducts() as $product) {
            $description[] = $product['cart_quantity'] . ' × ' . $product['name'];
        }
        $merchant_id = Configuration::get('COINTOPAY_IBAN_MERCHANT_ID');
        $security_code = Configuration::get('COINTOPAY_IBAN_SECURITY_CODE');
        $user_currency = !empty($selected_currency) ? $selected_currency : Configuration::get('COINTOPAY_IBAN_CRYPTO_CURRENCY');
        $selected_currency = !empty($user_currency) ? $user_currency : 1;
        $ctpConfig = [
            'merchant_id' => $merchant_id,
            'security_code' => $security_code,
            'selected_currency' => $selected_currency,
            'user_agent' => 'Cointopay - Prestashop v' . _PS_VERSION_ . ' Extension v' . COINTOPAY_IBAN_PRESTASHOP_EXTENSION_VERSION,
        ];
        if (!class_exists('Cointopay_Iban\Merchant\Order')) {
            throw new Exception('Cointopay_Iban\Merchant\Order class not found.');
        }
        $orderObj = new \Cointopay_Iban\Merchant\Order($this->module->currentOrder);

        \cointopay_iban\Cointopay_Iban::config($ctpConfig);
        $order = \Cointopay_Iban\Merchant\Order::createOrFail([
            'order_id' => implode('----', [$orderObj->reference, $this->module->currentOrder]),
            'price' => $total,
            'currency' => $this->currencyCode($currency->iso_code),
            'cancel_url' => $this->flashEncode($this->context->link->getModuleLink('cointopay_iban', 'cancel')),
            'callback_url' => $this->flashEncode($this->context->link->getModuleLink('cointopay_iban', 'callback')),
            'success_url' => $success_url,
            'title' => Configuration::get('PS_SHOP_NAME') . ' Order #' . $cart->id,
            'description' => implode(', ', $description),
            'selected_currency' => $selected_currency,
        ]);

        if (isset($order)) {
            $params = [
                'id_cart' => $cart->id,
                'id_module' => $this->module->id,
                'key' => $customer->secure_key,
                'id_order' => $this->module->currentOrder,
                'QRCodeURL' => $order->QRCodeURL,
                'TransactionID' => $order->TransactionID,
                'PaymentDetail' => $order->PaymentDetail,
                'Status' => $order->Status,
                'CoinName' => $order->CoinName,
                'RedirectURL' => $order->shortURL,
                'merchant_id' => $merchant_id,
                'ExpiryTime' => $order->ExpiryTime,
                'Amount' => $order->Amount,
                'CustomerReferenceNr' => $order->CustomerReferenceNr,
                'coinAddress' => $order->coinAddress,
                'ConfirmCode' => $order->Security,
                'AltCoinID' => $order->AltCoinID,
                'SecurityCode' => $order->SecurityCode,
                'inputCurrency' => $order->inputCurrency,
                'ChainName' => $order->ChainName,
            ];
            if (is_string($order)) {
                $validation_url = $this->context->link->getModuleLink('cointopay_iban', 'cointopay_validation', ['ctp_response' => $order], true);
                Tools::redirect($validation_url);
            } elseif (null != $order->Tag && $order->Tag != '') {
                $params['CtpTag'] = $order->Tag;
            }
            $confirmation_url = $link->getPageLink('order-confirmation', null, null, $params);
            Tools::redirect($confirmation_url);
        } else {
            Tools::redirect('index.php?controller=order&step=3');
        }
    }
    /**
     * URL encode to UTF-8
     *
     * @param $input
     * @return string
     */
    public function flashEncode($input)
    {
        return rawurlencode(mb_convert_encoding($input, 'UTF-8', mb_list_encodings()));
    }
    /**
     * Currency code
     *
     * @param $isoCode
     * @return string
     */
    public function currencyCode($isoCode)
    {
        $currencyCode = $isoCode;

        if (isset($isoCode) && ($isoCode == 'RUB')) {
            $currencyCode = 'RUR';
        }

        return $currencyCode;
    }
}
