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

use CDev\XPaymentsConnector\Controller\RegistryConstants;

/**
 * Payment Configuration model
 */
class PaymentConfiguration extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\DataObject\IdentityInterface
{
    /**
     * Cache tag
     */
    const CACHE_TAG = 'xpc_payment_configuration';

    /**
     * Cache tag
     */
    protected $_cacheTag = self::CACHE_TAG;

    /**
     * Event prefix
     */
    protected $_eventPrefix = 'xpc_payment_configuration';

    /**
     * Core registry
     */
    protected $coreRegistry = null;

    /**
     * Payment Card collection factory
     */
    protected $paymentConfigurationCollectionFactory = null;

    /**
     * Constructor
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \CDev\XPaymentsConnector\Model\ResourceModel\PaymentConfiguration\CollectionFactory $paymentConfigurationCollectionFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     *
     * @return void
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \CDev\XPaymentsConnector\Model\ResourceModel\PaymentConfiguration\CollectionFactory $paymentConfigurationCollectionFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = array()
    ) {

        $this->paymentConfigurationCollectionFactory = $paymentConfigurationCollectionFactory;

        parent::__construct($context, $coreRegistry, $resource, $resourceCollection, $data);

        $this->coreRegistry = $coreRegistry;
    }

    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('CDev\XPaymentsConnector\Model\ResourceModel\PaymentConfiguration');
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

    /**
     * Get default values
     *
     * @return array
     */
    public function getDefaultValues()
    {
        $values = array();

        return $values;
    }

    /**
     * Get payment configurations collection for current store
     *
     * @return \CDev\XPaymentsConnector\Model\ResourceModel\PaymentConfiguration\Collection
     */
    public function getCurrentStoreCollection()
    {
        $storeId = (int)$this->coreRegistry->registry(RegistryConstants::CURRENT_STORE_ID);

        $collection = $this->paymentConfigurationCollectionFactory->create();
        $collection->addFieldToFilter('store_id', $storeId);

        return $collection;
    }
}
