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
 * @author     Qualiteam Software <info@x-cart.com>
 * @category   CDev
 * @package    CDev_XPaymentsConnector
 * @copyright  (c) 2010-present Qualiteam software Ltd <info@x-cart.com>. All rights reserved
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * IFRAME actions
 */
var XPC_IFRAME_DO_NOTHING       = 0;
var XPC_IFRAME_CHANGE_METHOD    = 1;
var XPC_IFRAME_CLEAR_INIT_DATA  = 2;
var XPC_IFRAME_ALERT            = 3;
var XPC_IFRAME_TOP_MESSAGE      = 4;


var xpcMessages = [
  'paymentFormSubmitError',
  'paymentFormSubmit',
  'ready',
  'showMessage'
];

addEventListener('message', xpcMessageListener, false);

/**
 * Submit payment in X-Payments
 */
function submitPaymentForm()
{
    jQuery('.action-default').addClass('disabled');
    jQuery('body').trigger('processStart');

    var message = {
        message: 'submitPaymentForm',
        params: {}
    };

    message = JSON.stringify(message);

    jQuery('.xpc-iframe').get(0).contentWindow.postMessage(message, '*');
}

/**
 * Get XPC event message
 */
function getXpcMessage(event)
{
    var msg = false;

    try {
        msg = _.isString(event.data)
            ? JSON.parse(event.data)
            : event.data;

        if (0 > xpcMessages.indexOf(msg.message)) {
            msg = false;
        } else if (!msg.params) {
            msg.params = {};
        }

    } catch (e) {

        console.log(e);
    }

    return msg;
}

/**
 * Message listener
 */
function xpcMessageListener(event)
{
    var msg = getXpcMessage(event);

    if (!msg) {
        return;
    }

    var displayMessage = '';

    if (msg.params.message) {
        displayMessage = msg.params.message;
    } else if (msg.params.error) {
        displayMessage = msg.params.error;
    }

    if ('ready' == msg.message) {

        // Payment form is loaded in iframe

        jQuery('.xpc-iframe').css('height', msg.params.height + 'px');
        jQuery('.xpc-iframe').trigger('processStop');
        jQuery('.action-default').removeClass('disabled');
    }

    if ('paymentFormSubmitError' == msg.message) {

        // Error at X-Payments

        jQuery('.xpc-iframe').trigger('processStop');
        jQuery('.action-default').removeClass('disabled');

        alert(displayMessage);
     }

    if ('paymentFormSubmit' == msg.message) {

        // Payment form is submitted from X-Payments

        submitPaymentForm();
     }
}
