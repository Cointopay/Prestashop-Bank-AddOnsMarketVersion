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

{extends "$layout"}
<style>
#wrapper {
    background: #fff;
    box-shadow: inset 0 2px 5px 0 rgba(0,0,0,.11);
    padding-top: 1.563rem;
}
</style>
{block name="content"}
  <section style="padding:40px 0 60px 0;margin-bottom:50px;">
  <img src="/modules/cointopay/views/img/success-ctp.png" style="display:inline-block;float:left;margin-right: 10px;" width="53">
    <div style="display:inline-block;float:left;">
    <h1>Cointopay Payment Status:</h1>
    {$text|escape:'htmlall':'UTF-8'}
	</div>
  </section>
{/block}