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
 * @category   Cdev
 * @package    Cdev_XPaymentsConnector
 * @copyright  (c) 2010-present Qualiteam software Ltd <info@x-cart.com>. All rights reserved
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Cdev\XPaymentsConnector\Model;

/**
 * Payment Configuration model
 */
class PaymentConfiguration extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\DataObject\IdentityInterface
{
    /**
     * Cache tag
     * TODO: is it necesary?
     */
    const CACHE_TAG = 'xpc_payment_configuration';

    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Cdev\XPaymentsConnector\Model\ResourceModel\PaymentConfiguration');
    }

    /**
     * Get identities
     *
     * @return array
     */
    public function getIdentities()
    {
        return array(
            self::CACHE_TAG . '_' . $this->getId()
        );
    }
}
