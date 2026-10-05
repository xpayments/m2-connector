// vim: set ts=2 sw=2 sts=2 et:
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/license/OSL-3.0
 *
 * @author     X-Cart Holdings LLC <info@x-cart.com>
 * @category   CDev
 * @package    CDev_XPaymentsConnector
 * @copyright  (c) 2010-present X-Cart Holdings LLC <info@x-cart.com>. All rights reserved
 * @license    https://opensource.org/license/OSL-3.0  Open Software License (OSL 3.0)
 */

/**
 * Go to tab
 *
 * @param string tab Tab name
 *
 * @return void
 */
function goToTab(tab) 
{
    jQuery('a[name="' + tab + '"]').click();
}

/**
 * Confirm that user indeed wants to submit empty bundle
 *
 * @return bool
 */
function checkEmptyBundle()
{
    var bundle = jQuery('#xpc-bundle').val();

    if (!bundle) {

        var result = confirm('X-Payments configuration bundle is empty!');

    } else {

        var result = true;
    }

    return result;
}

/**
 * Switch allowed countries select-box
 * 
 * @return void
 */
function switchCountries()
{
    var disabled = ('0' == jQuery('#xpc_settings_payment_method_allow_specific').val());

    jQuery('#xpc_settings_payment_method_specific_country').attr('disabled', disabled);
}

/**
 * Set update form mode parameter
 *
 * @paramm string mode Mode
 *
 * @return void
 */
function setFormMode(mode)
{
    jQuery('input[name="mode"]').val(mode);
}

/**
 * Set update form mode parameter
 *
 * @paramm string mode Mode
 *
 * @return void
 */
function submitForm(mode)
{
    if ('undefined' != typeof mode) {
        setFormMode(mode);
    }

    jQuery('form[name="update_form"]:visible').get(0).submit();
}
