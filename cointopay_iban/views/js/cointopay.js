/**
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
*/
$(document).ready(function () {

    const merchant_id = $("#COINTOPAY_IBAN_MERCHANT_ID").val();
    getCoin(merchant_id);

    $("#COINTOPAY_IBAN_MERCHANT_ID").keyup(function () {
        getCoin(this.value);
    });
});

function getCoin(id) {

    var selected_currency = $('#selected_currency').val();
    var postdata = {
        ajax: 1,
        merchant: id,
        token: token
    };
    if (id.length > 0) {
        $.ajax({
            url: ctp_bank_coins_ajax_link,
            type: "POST",
            data: postdata,
            success: function (result) {
                var data = $.parseJSON(result);
                var str = "";
                var $crypto_currency = $('#crypto_currency');

                $.each(data, function (index, value) {
                    if (data[index].id != 0) {
                        str += "<option value='" + data[index].id + "'> " + data[index].name + "</option>";
                    }
                });

                $crypto_currency.html(str);
                if (selected_currency != '' && selected_currency != 0) {
                    $crypto_currency.val(selected_currency);
                }
            },
            error: function () {
                console.log("error");
            }
        });
    }
}