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
define([
    'Magento_Checkout/js/view/payment/default',
    'jquery',
    'mage/validation'
], function (Component) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'CDev_XPaymentsConnector/payment/xpc_payment_card',
            cardId: ''
        },

        /**
         * @return {Object}
         */
        getData: function () {
            return {
                method: this.item.method,
                'additional_data': {
                    'card_id': jQuery('input:radio[name="xpc_card_id"]:checked').val()
                }
            };
        },

        /**
         * @return {jQuery}
         */
        validate: function () {
            var form = 'form[data-role=xpc-payment-card-form]';

            // TDOD: implement JS validation

            return true;
        }
    });
});
