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
 * @category   XPay
 * @package    XPay_XPaymentsConnector
 * @copyright  (c) 2010-present Qualiteam software Ltd <info@x-cart.com>. All rights reserved
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace XPay\XPaymentsConnector\Model;

/**
 * XPC quote data model
 */
class QuoteData extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\DataObject\IdentityInterface
{
    /**
     * Token TTL
     */
    const TTL = 900;

    /**
     * Cache tag
     * TODO: is it necesary?
     */
    const CACHE_TAG = 'xpc_quote_data';

    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('XPay\XPaymentsConnector\Model\ResourceModel\QuoteData');
    }

    /**
     * Load data by name
     *
     * @param string $name Name
     *
     * @return \XPay\XPaymentsConnector\Model\QuoteData
     */
    public function loadByConf($quoteId, $confId)
    {
        $id = $this->_getResource()->loadByConf($this, $quoteId, $confId);
        
        $this->load($id);

        // TODO: Add expiration check

        return $this;
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
