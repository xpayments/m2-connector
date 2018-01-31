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
 * @category   Cdev
 * @package    Cdev_XPaymentsConnector
 * @copyright  (c) 2010-present Qualiteam software Ltd <info@x-cart.com>. All rights reserved
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
/*browser:true*/
/*global define*/
define(
    [
        'Magento_Checkout/js/view/payment/default',
        'Magento_Ui/js/model/messageList',
        'Magento_Checkout/js/model/full-screen-loader',
        'Magento_Ui/js/modal/alert',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/url-builder',
        'Magento_Customer/js/model/customer',
        'mage/storage',
    ],
    function (
        Component,
        globalMessageList,
        fullScreenLoader,
        modalAlert,
        quote,
        urlBuilder,
        customer,
        storage
    ) {
        'use strict';

        return Component.extend({
            defaults: {

                /**
                 * Payment method template
                 */
                template: 'Cdev_XPaymentsConnector/payment/form',

                /**
                 * Some component state flags
                 */
                ready: false,
                finalized: false,
                submitted: false,
                listerning: false,

                /**
                 * Iframe actions
                 */
                XPC_IFRAME_DO_NOTHING:      0,
                XPC_IFRAME_CHANGE_METHOD:   1,
                XPC_IFRAME_CLEAR_INIT_DATA: 2,
                XPC_IFRAME_ALERT:           3,
                XPC_IFRAME_TOP_MESSAGE:     4,

                /**
                 * List of iframe event messages
                 */
                xpcMessages: [
                  'paymentFormSubmitError',
                  'paymentFormSubmit',
                  'ready',
                  'showMessage',
                  'return'
                ],

                /**
                 * Magic making onActive event work
                 */
                imports: {
                    onActiveChange: 'active'
                }
            },

            /**
             * Set list of observable attributes
             *
             * @returns {exports.initObservable}
             */
            initObservable: function () 
            {
                this._super().observe(['active']);

                return this;
            },

            /**
             * Initialize payment method
             *
             * @return void
             */
            initialize: function() 
            {

                this._super();

                if (this.listerning) {
                    // TODO: Check. Probably it's single call in the core.
                    return false;
                }

                if (window.addEventListener) {
                    addEventListener('message', this.messageListener.bind(this), false);
                  } else {
                    // IE8. Really?
                    attachEvent('onmessage', this.messageListener.bind(this));
                }

                this.listerning = true;
            },

            /**
             * Check if payment is active
             *
             * @returns {Boolean}
             */
            isActive: function () 
            {
                var active = this.getCode() === this.isChecked();

                this.debug('ACTIVE', active);

                this.active(active);

                return active;
            },

            /**
             * Triggers when payment method change
             *
             * @param {Boolean} isActive
             *
             * @return void
             */
            onActiveChange: function (isActive) 
            {
                if (!isActive) {
                    return;
                }

                this.debug('ACTIVE CHANGE', isActive);

                this.reloadIframe();
            },

        /**
         * Returns state of place order button
         *
         * @returns {Boolean}
         */
        isButtonActive: function ()
        {
            return this.isActive() 
                && this.isPlaceOrderActionAllowed();
        },

            /**
             * Get payment method code
             *
             * @return string
             */
            getCode: function() 
            {
                return 'xpc';
            },

            /**
             * Get payment method data which is send to controller after order is submitted
             *
             * @return JSON
             */
            getData: function() 
            {
                return {
                    'method': this.item.method,
                    'additional_data': {}
                };
            },

            /**
             * Get Iframe redirect URL
             *
             * @return string
             */
            getUrl: function(dropToken) 
            {
                return dropToken
                    ? window.checkoutConfig.payment.xpc.url.dropTokenAndRedirect
                    : window.checkoutConfig.payment.xpc.url.redirect;
            },

            /**
             * Reload iframe
             *
             * @return void
             */
            reloadIframe: function(dropToken)
            {
                this.ready = false;

                var url = this.getUrl(dropToken);

                this.debug('LOAD IFRAME', url, this.getIframe());

                this.getIframe().attr('src', url);

                fullScreenLoader.startLoader();
            },

            /**
             * Convert event message to object
             *
             * @return object
             */
            getXpcMessage: function(event) 
            {
                var msg = false;

                try {
                    msg = _.isString(event.data)
                        ? JSON.parse(event.data)
                        : event.data;

                    if (0 > this.xpcMessages.indexOf(msg.message)) {
                        msg = false;
                    } else if (!msg.params) {
                        msg.params = {};
                    }

                } catch (e) {

                    this.debug(e);
                }

                return msg;
            },

            /**
             * Message listener
             *
             * @return void
             */
            messageListener: function (event)
            {
                this.debug(event);

                var msg = this.getXpcMessage(event);

                if (!msg) {
                    return;
                }

                var displayMessage = '';

                if (msg.params.message) {
                    displayMessage = msg.params.message;
                } else if (msg.params.error) {
                    displayMessage = msg.params.error;
                }

                if ('return' == msg.message) {

                    // Customer returned from X-Payments

                    this.finalized = true;
                    this.placeOrder();

                    return;
                }

                if ('paymentFormSubmitError' == msg.message) {

                    // Error at X-Payments

                    this.submitted = false;

                    fullScreenLoader.stopLoader();

                    var type = parseInt(msg.params.type);

                    if (this.XPC_IFRAME_CLEAR_INIT_DATA == type) {

                        this.reloadIframe(true);

                    } else if (this.XPC_IFRAME_CHANGE_METHOD == type) {

                        // TODO: implement
                        this.reloadIframe(true);
                    }

                    // Top message
                    globalMessageList.addErrorMessage({
                        message: displayMessage
                    });

                    return;
                }


                if ('ready' == msg.message) {

                    // Payment form is loaded in iframe

                    this.ready = true;

                    this.getIframe().css('height', msg.params.height + 'px');

                    fullScreenLoader.stopLoader();

                    return;
                }
            },

            /**
             * Get iframe element
             *
             * @return object
             */
            getIframe: function ()
            {
                return jQuery('#xpc-iframe');
            },

            /**
             * Send submit message to X-Payments
             *
             * @return void
             */
            sendSubmitMessage: function() 
            {
                if (this.submitted) {
                    return;
                }

                fullScreenLoader.startLoader();
                this.submitted = true;

                var message = {
                    message: 'submitPaymentForm',
                    params: {}
                };

                message = JSON.stringify(message);

                this.getIframe().get(0).contentWindow.postMessage(message, '*');
            },

            saveCheckoutData: function ()
            {
                var data = {
                    cartId: quote.getQuoteId(),
                    billingAddress: quote.billingAddress(),
                };

                if (!customer.isLoggedIn()) {
                    data.email = quote.guestEmail;
                }

                data = {'data': JSON.stringify(data)};

                console.log(data);

                return jQuery.post(
                    window.checkoutConfig.payment.xpc.url.saveCheckoutData,
                    data
                )
            },

            /**
             * Place order
             *
             * @return bool 
             */
            placeOrder: function () 
            {
                this.debug('Place order clicked');

                if (this.finalized) {
                    return this._super();
                }

                var self = this;

                if (!this.submitted) {
                    this.saveCheckoutData().done(
                        function () {
                            self.sendSubmitMessage();
                        }
                    );
                    return false;
                } 
            },

            /**
             * Log something to the console
             */
            debug: function()
            {
                if (window.checkoutConfig.payment.xpc.isDebug) {
                    console.log(arguments);
                }
            }
        });
    }
);
