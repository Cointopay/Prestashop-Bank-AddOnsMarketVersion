{**
 * 2007-2024 PrestaShop and Contributors
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/OSL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2024 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 *}

{capture name=path}
    <a href="{$link->getPageLink('order', true, NULL, "step=3")|escape:'html':'UTF-8'}" title="{l s='Go back to the Checkout' mod='cointopay_iban'}">
        {l s='Checkout' mod='cointopay_iban'}
    </a>
    <span class="navigation-pipe">{$navigationPipe|escape:'htmlall':'UTF-8'}</span>
    {l s='Cointopay payment' mod='cointopay_iban'}
{/capture}

<h1 class="page-heading">
    {l s='Order summary' mod='cointopay_iban'}
</h1>

{assign var='current_step' value='payment'}
{include file="$tpl_dir./order-steps.tpl"}

{if $nbProducts <= 0}
    <p class="alert alert-warning">
        {l s='Your shopping cart is empty.' mod='cointopay_iban'}
    </p>
{else}
    <form action="{$link->getModuleLink('cointopay_iban', 'redirect', [], true)|escape:'html':'UTF-8'}" method="post">
        <div class="box cheque-box">
            <h3 class="page-subheading">
                {l s='Cointopay payment' mod='cointopay_iban'}
            </h3>

            <p class="cheque-indent">
                <strong class="dark">
                    {l s='You have chosen to pay with Cryptocurrency via Cointopay.' mod='cointopay_iban'} {l s='Here is a short summary of your order:' mod='cointopay_iban'}
                </strong>
            </p>

            <p>
                - {l s='The total amount of your order is' mod='cointopay_iban'}
                <span id="amount" class="price">{displayPrice price=$total}</span>
                {if $use_taxes == 1}
                    {l s='(tax incl.)' mod='cointopay_iban'}
                {/if}
            </p>

            <p>
                - {l s='You will be redirected to Cointopay for payment with Cryptocurrency.' mod='cointopay_iban'}
                <br/>
                - {l s='Please confirm your order by clicking "I confirm my order".' mod='cointopay_iban'}
            </p>
        </div>
        <p class="cart_navigation clearfix" id="cart_navigation">
            <a class="button-exclusive btn btn-default" href="{$link->getPageLink('order', true, NULL, "step=3")|escape:'html':'UTF-8'}">
                <i class="icon-chevron-left"></i>{l s='Other payment methods' mod='cointopay_iban'}
            </a>
            <button class="button btn btn-default button-medium" type="submit">
                <span>{l s='I confirm my order' mod='cointopay_iban'}<i class="icon-chevron-right right"></i></span>
            </button>
        </p>
    </form>
{/if}