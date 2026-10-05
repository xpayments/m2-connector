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

namespace CDev\XPaymentsConnector\Controller\Payment\Card;

use CDev\XPaymentsConnector\Controller\RegistryConstants;

/**
 * Add new payment card iframe action
 */
class Iframe extends \CDev\XPaymentsConnector\Controller\Payment\Card
{
    /**
     * Page factory
     */
    protected $pageFactory = null;

    /**
     * Constructor
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Customer\Model\Session\Proxy $customerSession
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Framework\View\Result\PageFactory $pageFactory
     *
     * @return void
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Customer\Model\Session\Proxy $customerSession,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\View\Result\PageFactory $pageFactory
    ) {
        parent::__construct($context, $customerSession, $coreRegistry, $storeManager);

        $this->pageFactory = $pageFactory;
    }

    /**
     * Execute action
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $storeId = $this->initCurrentStoreId();

        $this->coreRegistry->register(
            RegistryConstants::ZERO_AUTH_TOKEN,
            $this->getRequest()->getParam('token')
        );

        return $this->pageFactory->create();
    }
}
