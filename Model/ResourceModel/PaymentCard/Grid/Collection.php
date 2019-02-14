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

namespace CDev\XPaymentsConnector\Model\ResourceModel\PaymentCard\Grid;

use CDev\XPaymentsConnector\Controller\RegistryConstants;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\Search\AggregationInterface;
use CDev\XPaymentsConnector\Model\ResourceModel\PaymentCard\Collection as PaymentCardCollection;

/**
 * Collection for displaying Payment Card grid
 */
class Collection extends PaymentCardCollection implements SearchResultInterface
{
    /**
     * Core registry
     */
    protected $coreRegistry = null;

    /**
     * Ignore Only dependency assignment operations are allowed in constructor
     * @codingStandardsIgnoreStart
     */

    /**
     * Constructor
     *
     * @param \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\Registry $coreRegistry
     * @param $mainTable
     * @param $eventPrefix
     * @param $eventObject
     * @param $resourceModel
     * @param $model
     * @param \Magento\Framework\DB\Adapter\AdapterInterface $connection
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource
     *
     * @return void
     *
     * @SuppressWarnings(MEQP2.Classes.ConstructorOperations.CustomOperationsFound)
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\Registry $coreRegistry,
        $mainTable,
        $eventPrefix,
        $eventObject,
        $resourceModel,
        $model = \Magento\Framework\View\Element\UiComponent\DataProvider\Document::class,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {

        $this->coreRegistry = $coreRegistry;

        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $connection,
            $resource
        );

        // This MUST be after the parent constructor
        $this->_eventPrefix = $eventPrefix;
        $this->_eventObject = $eventObject;
        $this->_init($model, $resourceModel);
        $this->setMainTable($mainTable);
    }

    /**
     * /Ignore Only dependency assignment operations are allowed in constructor
     * @codingStandardsIgnoreEnd
     */

    /**
     * @return AggregationInterface
     */
    public function getAggregations()
    {
        return $this->aggregations;
    }

    /**
     * @param AggregationInterface $aggregations
     *
     * @return $this
     */
    public function setAggregations($aggregations)
    {
        $this->aggregations = $aggregations;
    }

    /**
     * Get search criteria.
     *
     * @return \Magento\Framework\Api\SearchCriteriaInterface|null
     */
    public function getSearchCriteria()
    {
        return null;
    }

    /**
     * Set search criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     *
     * @return $this
     */
    public function setSearchCriteria(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria = null)
    {
        return $this;
    }

    /**
     * Get total count.
     *
     * @return int
     */
    public function getTotalCount()
    {
        return $this->getSize();
    }

    /**
     * Set total count.
     *
     * @param int $totalCount
     *
     * @return $this
     */
    public function setTotalCount($totalCount)
    {
        return $this;
    }

    /**
     * Set items list.
     *
     * @param \Magento\Framework\Api\ExtensibleDataInterface[] $items
     *
     * @return $this
     */
    public function setItems(array $items = null)
    {
        return $this;
    }

    /**
     * Initialize select query
     *
     * @return this
     */
    protected function _initSelect()
    {
        parent::_initSelect();

        $this->addFilterToMap(
            'card_number',
            new \Magento\Framework\DB\Sql\Expression('CONCAT(main_table.first6, "******", main_table.last4)')
        );

        $this->addFilterToMap(
            'customer_name',
            new \Magento\Framework\DB\Sql\Expression('CONCAT(ce.firstname, " ", ce.lastname)')
        );

        $customerId = $this->coreRegistry->registry(RegistryConstants::CURRENT_CUSTOMER_ID);

        if (null !== $customerId) {
            $this->addFieldToFilter('user_id', $customerId);
        }

        $storeId = (int)$this->coreRegistry->registry(RegistryConstants::CURRENT_STORE_ID);

        if ($storeId > 0) {
            $this->addFieldToFilter('main_table.store_id', $storeId);
        }

        $this->join(
            array('ce' => $this->getTable('customer_entity')),
            'main_table.user_id = ce.entity_id',
            array('email', 'firstname', 'lastname')
        );

        return $this;
    }
}
