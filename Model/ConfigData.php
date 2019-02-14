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
 * XPC config data model
 */
class ConfigData extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\DataObject\IdentityInterface
{
    /**
     * Cache tag
     */
    const CACHE_TAG = 'xpc_config_data';

    /**
     * Cache tag
     */
    protected $_cacheTag = self::CACHE_TAG;

    /**
     * Event prefix
     */
    protected $_eventPrefix = 'xpc_config_data';

    /**
     * Core registry
     */
    protected $coreRegistry = null;

    /**
     * App State
     */
    protected $appState = null;

    /**
     * Store ID
     */
    protected $storeId = null;

    /**
     * config collection factory
     */
    protected $configCollectionFactory = null;

    /**
     * Constructor
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\App\State $appState
     * @param \CDev\XPaymentsConnector\Model\ResourceModel\ConfigData\CollectionFactory $configCollectionFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     *
     * @return void
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\App\State $appState,
        \CDev\XPaymentsConnector\Model\ResourceModel\ConfigData\CollectionFactory $configCollectionFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = array()
    ) {

        $this->configCollectionFactory = $configCollectionFactory;

        parent::__construct($context, $coreRegistry, $resource, $resourceCollection, $data);

        $this->coreRegistry = $coreRegistry;
        $this->appState = $appState;
    }

    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('CDev\XPaymentsConnector\Model\ResourceModel\ConfigData');
    }

    /**
     * Get Store ID (it might be changed to zero)
     *
     * @return int
     */
    protected function getStoreId()
    {
        if (null === $this->storeId) {

            $this->storeId = (int)$this->coreRegistry->registry(RegistryConstants::CURRENT_STORE_ID);

            if (
                !$this->isConfiguredForStore($this->storeId)
                && $this->isConfiguredForStore(0)
            ) {

                // Switch to default store, if it's configured, and the current store is not configured
                $this->storeId = 0;
            }
        }

        return $this->storeId;
    }

    /**
     * Check if XPC is configured for store
     *
     * @param int $storeId Store ID
     *
     * @return \CDev\XPaymentsConnector\Model\ConfigData
     */
    protected function isConfiguredForStore($storeId)
    {
        $collection = $this->configCollectionFactory->create();

        $collection->addFieldToFilter('name', 'is_configured');
        $collection->addFieldToFilter('store_id', $storeId);

        return(bool)$collection->getFirstItem()->getValue();
    }

    /**
     * Load data by name
     *
     * @param string $name Name
     *
     * @return \CDev\XPaymentsConnector\Model\ConfigData
     */
    public function loadByName($name)
    {
        $storeId = $this->getStoreId();

        $collection = $this->configCollectionFactory->create();

        $collection->addFieldToFilter('name', $name);
        $collection->addFieldToFilter('store_id', $storeId);

        return $collection->getFirstItem();
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
}
