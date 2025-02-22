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

<div class="tab">
  <button class="tablinks" onclick="changeTab(event, 'Information')" id="defaultOpen">Information</button>
  <button class="tablinks" onclick="changeTab(event, 'Configure Settings')">Configure Settings</button>
</div>

<!-- Tab content -->
<div id="Information" class="tabcontent">
	<div class="wrapper">
	  <img alt="" src="../modules/cointopay_iban/views/img/logo.png" style="float:right;"/>
	  <h2 class="cointopay-information-header">Accept payments on your Prestashop store with Cointopay</h2><br/>
	  <strong>What is Cointopay? </strong> <br/>
	  <p>We offer a fully automated cryptocurrency-, bank and card processing platform and invoice system. Accept any cryptocurrency and get paid in Euros or U.S. Dollars directly to your bank account (for verified merchants), or just keep it in crypto like bitcoin!</p><br/>
	  <strong>Getting started</strong><br/>
	  <div>
	  	<ul>
	  		<li>Install the Cointopay Pay via Bank module on PrestaShop</li>
	  		<li>Visit cointopay.com and create an account</li>
	  		<li>Get your API Key, Merchant ID and Security Code copy-paste them to the Configuration page in Cointopay module</li>
	  	</ul>
	  </div>
	  <p class="sign-up"><br/>
	  	<a href="https://cointopay.com/signup" class="sign-up-button">Sign up on Cointopay</a>
	  </p><br/>
	  <strong>Features</strong>
	  <div>
	  	<ul>
	  		<li>The gateway is <strong>fully automatic</strong> - set and forget it.</li>
	  		<li>Payment amount is calculated using <strong> real-time exchange rates</strong>.</li>
	  		<li>Your customers can select to pay via Bank at checkout, while your payouts are in single currency of your choice.</li>
	  		<li><strong> No chargebacks</strong> - guaranteed!</li>
	  	</ul>
	  </div>
	</div>
</div>

<div id="Configure Settings" class="tabcontent">
  {html_entity_decode($form|escape:'htmlall':'UTF-8')}
  <input type="hidden" name="selected_currency" id="selected_currency" value="{html_entity_decode($selected_currency|escape:'htmlall':'UTF-8')}" >
</div>


<script type="text/javascript">
	document.getElementById("defaultOpen").click();
</script>