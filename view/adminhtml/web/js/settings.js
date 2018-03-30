// vim: set ts=2 sw=2 sts=2 et:
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @author Qualiteam Software <info@x-cart.com>
 * @category   CDev
 * @packageCDev_XPaymentsConnector
 * @copyright  (c) 2010-present Qualiteam software Ltd <info@x-cart.com>. All rights reserved
 * @licensehttp://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
