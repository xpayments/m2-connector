<?php
// vim: set ts=4 sw=4 sts=4 et:
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

namespace CDev\XPaymentsConnector\Controller;

/**
 * Declarations of core registry keys used by the X-Payments Connector module
 */
class RegistryConstants
{
    /**
     * Registry key where current customer ID is stored
     */
    const CURRENT_CUSTOMER_ID = 'xpc_current_customer_id';

    /**
     * Registry key for zero-auth token
     */
    const ZERO_AUTH_TOKEN = 'xpc_zero_auth_token';

    /**
     * Registry key where current store ID is stored
     */
    const CURRENT_STORE_ID = 'xpc_current_store_id';
}
