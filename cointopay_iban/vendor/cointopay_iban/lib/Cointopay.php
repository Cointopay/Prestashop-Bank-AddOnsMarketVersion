<?php
namespace cointopay_iban;
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

class Cointopay_Iban
{
    const VERSION = '1.0';
    const USER_AGENT_ORIGIN = 'Cointopay PHP Library';
    public static $merchant_id = '';
    public static $security_code = '';
    public static $default_currency = '';
    public static $user_agent = '';
    public static $selected_currency = '';

    public static function config($authentication)
    {
        if (isset($authentication['merchant_id']))
            self::$merchant_id = $authentication['merchant_id'];

        if (isset($authentication['security_code']))
            self::$security_code = $authentication['security_code'];

        if (isset($authentication['default_currency']))
            self::$default_currency = $authentication['default_currency'];

        if (isset($authentication['user_agent']))
            self::$user_agent = $authentication['user_agent'];

        if (isset($authentication['selected_currency']))
            self::$selected_currency = $authentication['selected_currency'];
    }
    public static function verifyMerchant($authentication = array())
    {
        try {
            $response = self::request('merchant', 'GET', array(), $authentication);
            if ($response != "testmerchant success") {
                return $response;
            }
            return true;
        } catch (\Exception $e) {
            return get_class($e) . ': ' . $e->getMessage();
        }
    }
    public static function request($url, $method = 'GET', $params = array(), $authentication = array())
    {
        $merchant_id = isset($authentication['merchant_id']) ? $authentication['merchant_id'] : self::$merchant_id;
        $security_code = isset($authentication['security_code']) ? $authentication['security_code'] : self::$security_code;
        $selected_currency = isset($authentication['selected_currency']) ? $authentication['selected_currency'] : self::$selected_currency;
        $user_agent = isset($authentication['user_agent']) ? $authentication['user_agent'] : (isset(self::$user_agent) ? self::$user_agent : (self::USER_AGENT_ORIGIN . ' v' . self::VERSION));
        $request_check = '';

        # Check if credentials was passed
        if (empty($merchant_id) || empty($security_code))
            \Cointopay_Iban\Exception::throwException(400, array('reason' => 'CredentialsMissing'));

        if ($url == 'merchant') {
            if (isset($params) && !empty($params)) {
                $amount = $params['price'];
                $order_id = $params['order_id'];
                $currency = $params['currency'];
                $callback_url = $params['callback_url'];
                $cancel_url = $params['cancel_url'];
                $selected_currency = (isset($params['selected_currency']) && !empty($params['selected_currency'])) ? $params['selected_currency'] : 1;
            }

            $request_check = 'merchant';
            $url = "MerchantAPI?Checkout=true&MerchantID=$merchant_id&Amount=10&AltCoinID=$selected_currency&CustomerReferenceNr=testmerchant&SecurityCode=$security_code&inputCurrency=EUR&output=json&testmerchant";

            return self::callApi($url, $user_agent);
        } elseif ($url == 'validation') {
            if (isset($params) && !empty($params)) {
                $ConfirmCode = $params['ConfirmCode'];
                $selected_currency = (isset($params['selected_currency']) && !empty($params['selected_currency'])) ? $params['selected_currency'] : 1;
            }
            $url = "v2REAPI?MerchantID=$merchant_id&Call=Transactiondetail&APIKey=a&output=json&ConfirmCode=$ConfirmCode";

            return self::callApi($url, $user_agent);
        } else {
            if (isset($params) && !empty($params)) {
                $amount = $params['price'];
                $order_id = $params['order_id'];
                $currency = $params['currency'];
                $callback_url = $params['callback_url'];
                $cancel_url = $params['cancel_url'];
                $selected_currency = (isset($params['selected_currency']) && !empty($params['selected_currency'])) ? $params['selected_currency'] : 1;
            }
            $currency = $params['currency'];

            $url = "MerchantAPI?Checkout=true&MerchantID=$merchant_id&Amount=$amount&AltCoinID=$selected_currency&CustomerReferenceNr=$order_id&SecurityCode=$security_code&inputCurrency=$currency&output=json&transactionconfirmurl=$callback_url&transactionfailurl=$cancel_url";
            $result = self::callApi($url, $user_agent);

            if ($result == 'testmerchant success') {

                $url = "MerchantAPI?Checkout=true&MerchantID=$merchant_id&Amount=$amount&AltCoinID=$selected_currency&CustomerReferenceNr=$order_id&SecurityCode=$security_code&output=json&inputCurrency=$currency&transactionconfirmurl=$callback_url&transactionfailurl=$cancel_url";
                return self::callApi($url, $user_agent);
            } else {
                return $result;
            }
        }
    }
    public static function callApi($url, $user_agent)
    {

        $url = 'https://cointopay.com/' . $url;

        $curl = curl_init();
        curl_setopt_array(
            $curl,
            array(
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_URL => $url,
                CURLOPT_USERAGENT => $user_agent
            )
        );
        $response = curl_exec($curl);

        curl_close($curl);

        $result_ctp = json_decode($response, true);
        if (is_string($result_ctp) && $result_ctp != 'testmerchant success') {
            return $result_ctp;
        }
        return $result_ctp;
    }
}