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

namespace CDev\XPaymentsConnector\Controller\Adminhtml\Payment;

use CDev\XPaymentsConnector\Controller\RegistryConstants;

/**
 * Basic Payment Card controller
 */
abstract class Card extends \Magento\Backend\App\Action
{
    /**
     * Core registry
     */
    protected $coreRegistry = null;

    /**
     * Check if action is allowed
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('CDev_XPaymentsConnector::payment_card');
    }

    /**
     * Constructor
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     *
     * @return void
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry
    ) {
        parent::__construct($context);

        $this->coreRegistry = $coreRegistry;
    }

    /**
     * Initialize customer by ID specified in request
     *
     * @return int
     */
    protected function initCurrentCustomer()
    {
        $customerId = (int)$this->getRequest()->getParam('user_id');

        if ($customerId) {
            $this->coreRegistry->register(RegistryConstants::CURRENT_CUSTOMER_ID, $customerId);
        }

        return $customerId;
    }

    /**
     * Initialize current Store ID
     *
     * @return int
     */
    protected function initCurrentStoreId()
    {
        $storeId = (int)$this->getRequest()->getParam('store', 0);

        if (!$storeId) {
            // Magento uses `store`, we use `store_id`. So check both. TODO: Correct store-switcher
            $storeId = (int)$this->getRequest()->getParam('store_id', 0);
        }

        $this->coreRegistry->register(RegistryConstants::CURRENT_STORE_ID, $storeId);

        return $storeId;
    }
}
