<?php
namespace Cointopay_Iban\Merchant;
if (!defined('_PS_VERSION_')) {
    exit;
}

use Cointopay_Iban\Cointopay_Iban;
use Cointopay_Iban\Merchant;

class Order extends Merchant
{
    private $order;

    public function __construct($order)
    {
        $this->order = $order;
    }

    public static function createOrFail($params, $options = [], $authentication = [])
    {
        $order = Cointopay_Iban::request('orders', 'GET', $params, $authentication);

        if (is_string($order) && $order !== 'testmerchant success') {
            return $order;
        } elseif (is_array($order)) {
            return new self($order);
        } else {
            throw new \Exception('Invalid order response from Cointopay.');
        }
    }

    public static function ValidateOrder($params, $options = [], $authentication = [])
    {
        $order = Cointopay_Iban::request('validation', 'GET', $params, $authentication);
        
        if (!is_array($order)) {
            throw new \Exception('Invalid validation response from Cointopay.');
        }

        return new self($order);
    }

    public function toHash()
    {
        return $this->order;
    }

    public function __get($name)
    {
        return isset($this->order[$name]) ? $this->order[$name] : null;
    }
}