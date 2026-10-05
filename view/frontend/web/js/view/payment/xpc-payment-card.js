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
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (Component, rendererList) {
        'use strict';
 
        rendererList.push(
            {
                type: 'xpc_payment_card',
                component: 'CDev_XPaymentsConnector/js/view/payment/method-renderer/xpc-payment-card'
            }
        );
 
        /** Add view logic here if needed */
        return Component.extend({});
    });
