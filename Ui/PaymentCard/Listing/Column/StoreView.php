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
 * https://opensource.org/license/OSL-3.0
 *
 * @author     X-Cart Holdings LLC <info@x-cart.com>
 * @category   CDev
 * @package    CDev_XPaymentsConnector
 * @copyright  (c) 2010-present X-Cart Holdings LLC <info@x-cart.com>. All rights reserved
 * @license    https://opensource.org/license/OSL-3.0  Open Software License (OSL 3.0)
 */

namespace CDev\XPaymentsConnector\Ui\PaymentCard\Listing\Column;

use CDev\XPaymentsConnector\Controller\RegistryConstants;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * "Store View" column for payment cards grid
 */
class StoreView extends Column
{
    /**
     * Store manager
     */
    protected $storeManager = null;

    /**
     * Core registry
     */
    protected $coreRegistry = null;
 
    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\UiComponent\ContextInterface $context
     * @param \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Registry $registry
     * @param array $components
     * @param array $data
     *
     * @return void
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Registry $coreRegistry,
        array $components = array(),
        array $data = array()
    ) {

        parent::__construct($context, $uiComponentFactory, $components, $data);

        $this->coreRegistry = $coreRegistry;
        $this->storeManager = $storeManager;
    }

    /**
     * Prepare data source, set card number
     *
     * @param array $dataSource Data source
     *
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {

            $stores = $this->storeManager->getStores();

            $storeNames = array();

            foreach ($stores as $store) {
                $storeNames[$store->getId()] = $store->getName();
            }

            foreach ($dataSource['data']['items'] as $key => & $item) {

                $item['store_view'] = !empty($storeNames[$item['store_id']]) ? $storeNames[$item['store_id']] : '';
            }
        }

        return $dataSource;
    }

    /**
     * Prepare component configuration
     *
     * @return void
     */
    public function prepare()
    {
        parent::prepare();

        $storeId = (int)$this->coreRegistry->registry(RegistryConstants::CURRENT_STORE_ID);

        // Disable store-view column if specific store-view is selected
        if (0 < $storeId) {
            $this->_data['config']['componentDisabled'] = true;
        }
    }
}
