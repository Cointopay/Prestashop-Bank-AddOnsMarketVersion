{**
 * 2007-2025 PrestaShop and Contributors
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
 * @copyright 2007-2025 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 *}
<section id="cointopay_order_confirmation_section">
    <h3 class="h3 card-title">Cointopay Payment details:</h3>
    <div class="cointopay-login-content">
        <p>To pay with Cointopay <a class="inline_popup_cointopay" href="#" rel="nofollow">Click here</a></p>

        <div id="cointopay-iban-modal-6-0" class="modal fade cointopay_popup in" tabindex="-1" role="dialog"
             style="display: block;">
            <div class="modal-dialog modal-dialog-centered" role="document"
                 style="max-width: 1150px;margin-left:auto;margin-right:auto;display:table;">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <h3 class="h3 card-title">Cointopay Payment details:</h3>

                        <div class="row">

                            <div class="col-md-12 col-sm-12" style="margin-bottom:40px;">
                                <table class="form">
                                    <tbody>
                                    <tr style="height: 50px;">
                                        <td style="width: 200px;">Checkout#</td>
                                        <td>{$smarty.get.CustomerReferenceNr|escape:'htmlall':'UTF-8'}</td>
                                    </tr>
                                    <tr style="height: 50px;">
                                        <td style="width: 200px;">Status</td>
                                        <td>{$smarty.get.Status|escape:'htmlall':'UTF-8'}</td>
                                    </tr>
                                    <tr style="height: 50px;">
                                        <td style="width: 200px;">Expiry</td>
                                        <td>
                                            <span id="expire_time">{$smarty.get.ExpiryTime|escape:'htmlall':'UTF-8'} </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="2">
                                            <span class="content-center">{$smarty.get.PaymentDetail|cleanHtml nofilter}</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="width: 200px;">For more payment details</td>
                                        <td><a href="{$smarty.get.RedirectURL|escape:'htmlall':'UTF-8'}" style=""
                                               target="_blank">Click here</a></td>
                                    </tr>
                                    </tbody>
                                </table>

                            </div>
                        </div>
                    </div>
                    <div class="modal-footer"></div>
                </div>
            </div>
        </div>
    </div>
</section>
<script src="https://code.jquery.com/jquery-3.4.1.min.js"
        integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo=" crossorigin="anonymous"></script>
<form method="post" action="{$ctpCallbackUrl|escape:'htmlall':'UTF-8'}" id="CoinsPaymentCallBack">
    <input type="hidden" name="CustomerReferenceNr" id="CustomerReferenceNr"
           value="{$smarty.get.CustomerReferenceNr|escape:'htmlall':'UTF-8'}"/>
    <input type="hidden" name="ConfirmCode" id="ConfirmCode"
           value="{$smarty.get.ConfirmCode|escape:'htmlall':'UTF-8'}"/>
    <input type="hidden" name="status" id="CoinsPaymentStatus" value=""/>
    <input type="hidden" name="notenough" id="CoinsPaymentNotEnough" value=""/>
    <input type="hidden" name="COINTOPAY_IBAN_MERCHANT_ID" id="COINTOPAY_IBAN_MERCHANT_ID"
           value="{$smarty.get.merchant_id|escape:'htmlall':'UTF-8'}"/>
    <input type="hidden" name="TransactionID" id="COINTOPAY_IBAN_TransactionID"
           value="{$smarty.get.TransactionID|escape:'htmlall':'UTF-8'}"/>
    <input type="hidden" name="CoinAddressUsed" id="CoinAddressUsed"
           value="{$smarty.get.coinAddress|escape:'htmlall':'UTF-8'}"/>
    <input type="hidden" name="SecurityCode" id="SecurityCode"
           value="{$smarty.get.SecurityCode|escape:'htmlall':'UTF-8'}"/>
    <input type="hidden" name="AltCoinID" id="AltCoinID" value="{$smarty.get.AltCoinID|escape:'htmlall':'UTF-8'}"/>
    <input type="hidden" name="RedirectURL" id="RedirectURL"
           value="{$smarty.get.RedirectURL|escape:'htmlall':'UTF-8'}"/>
    <input type="hidden" name="ajaxUrlCTP" id="ajaxUrlCTP" value="{$ctpAjaxUrl|escape:'htmlall':'UTF-8'}"/>
</form>
<script type="text/javascript">
    jQuery(document).ready(function ($) {
        jQuery('#cointopay-iban-modal-6-0').modal('show');
        jQuery('.inline_popup_cointopay').click(function () {
            jQuery('#cointopay-iban-modal-6-0').modal('show');
        });
        $('html, body').animate({
            scrollTop: $('#cointopay_order_confirmation_section').offset().top
        }, 'slow')

        const d1 = new Date(),
            d2 = new Date(d1);
        d2.setMinutes(d1.getMinutes() + {$smarty.get.ExpiryTime|escape:'htmlall':'UTF-8'});
        const countDownDate = d2.getTime();
        // Update the countdown every 1 second
        const x = setInterval(function () {
            if ($('#expire_time').length) {
                // Get today's date and time
                const now = new Date().getTime();

                // Find the distance between now the countdown date
                const distance = countDownDate - now;

                // Time calculations for days, hours, minutes and seconds
                const days = Math.floor(distance / (1000 * 60 * 60 * 24));
                const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((distance % (1000 * 60)) / 1000);

                // Output the result in an element with id="expire_time"
                document.getElementById("expire_time").innerHTML = days + "d " + hours + "h "
                    + minutes + "m " + seconds + "s ";

                // If the countdown is over, write some text
                if (distance < 0) {
                    clearInterval(x);
                    document.getElementById("expire_time").innerHTML = "EXPIRED";
                }
            }
        }, 1000);
    });
</script>

