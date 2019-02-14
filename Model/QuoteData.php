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

namespace CDev\XPaymentsConnector\Model;

/**
 * XPC quote data model
 */
class QuoteData extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\DataObject\IdentityInterface
{
    /**
     * Payment token TTL
     */
    const TOKEN_TTL = 900;

    /**
     * Cache tag
     */
    const CACHE_TAG = 'xpc_quote_data';

    /**
     * Cache tag
     */
    protected $_cacheTag = self::CACHE_TAG;

    /**
     * Event prefix
     */
    protected $_eventPrefix = 'xpc_quote_data';

    /**
     * Date
     */
    protected $date = null;

    /**
     * XPC Helper
     */
    protected $helper = null;

    /**
     * Constructor
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \CDev\XPaymentsConnector\Helper\Data $helper
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     *
     * @return void
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \CDev\XPaymentsConnector\Helper\Data $helper,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = array()
    ) {

        parent::__construct($context, $coreRegistry, $resource, $resourceCollection, $data);

        $this->date = $date;

        $this->helper = $helper;
    }

    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('CDev\XPaymentsConnector\Model\ResourceModel\QuoteData');
    }

    /**
     * Load data by Quote ID
     *
     * @param int $quoteId Quote ID
     *
     * @return \CDev\XPaymentsConnector\Model\QuoteData
     */
    public function loadByQuoteId($quoteId)
    {
        $confId = $this->helper->settings->getXpcConfig('active_confid');

        $id = $this->_getResource()->loadByQuoteAndConf($quoteId, $confId);
        
        $this->load($id);

        return $this;
    }

    /**
     * Load data by Quote
     *
     * @param \Magento\Quote\Model\Quote $quote Quote
     *
     * @return \CDev\XPaymentsConnector\Model\QuoteData
     */
    public function loadByQuote(\Magento\Quote\Model\Quote $quote)
    {
        return $this->loadByQuoteId($quote->getId());
    }

    /**
     * Set payment session expiration
     *
     * @return $this
     */
    public function setExpiration()
    {
        $expires = (int)$this->date->gmtDate('U') + self::TOKEN_TTL;

        $this->setExpires($expires);

        return $this;
    }

    /**
     * Check if payment session is expired
     *
     * @return bool
     */
    public function isExpired()
    {
        $currentTime = (int)$this->date->gmtDate('U');

        $expireTime = (int)$this->date->gmtDate('U', $this->getExpires());

        return $expireTime <= $currentTime;
    }

    /**
     * Check if XPC data is valid: token, txnId, expiration
     *
     * @return bool
     */
    public function isValid()
    {
        return !empty($this->getToken())
            && !empty($this->getTxnid())
            && !$this->isExpired();
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
