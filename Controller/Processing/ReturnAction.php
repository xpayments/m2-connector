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

namespace CDev\XPaymentsConnector\Controller\Processing;

use CDev\XPaymentsConnector\Controller\RegistryConstants;

/**
 * Return from payment form (in iframe)
 */
class ReturnAction extends \Magento\Framework\App\Action\Action
{
    /**
     * Session models
     */
    protected $customerSession = null;
    protected $checkoutSession = null;

    /**
     * Result page factory
     */
    protected $pageFactory = null;

    /**
     * XPC Quote Data factory
     */
    protected $quoteDataFactory = null;

    /**
     * XPC Helper
     */
    protected $helper = null;

    /**
     * Core coreRegistry
     */
    protected $coreRegistry = null;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $pageFactory
     * @param \Magento\Checkout\Model\Session\Proxy $checkoutSession
     * @param \Magento\Customer\Model\Session\Proxy $customerSession
     * @param \CDev\XPaymentsConnector\Helper\Data $helper
     * @param \CDev\XPaymentsConnector\Model\QuoteDataFactory $quoteDataFactory
     *
     * @return void
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $pageFactory,
        \Magento\Checkout\Model\Session\Proxy $checkoutSession,
        \Magento\Customer\Model\Session\Proxy $customerSession,
        \CDev\XPaymentsConnector\Helper\Data $helper,
        \CDev\XPaymentsConnector\Model\QuoteDataFactory $quoteDataFactory,
        \Magento\Framework\Registry $coreRegistry
    ) {
        $this->pageFactory = $pageFactory;
        $this->helper = $helper;
        $this->customerSession = $customerSession;
        $this->checkoutSession = $checkoutSession;
        $this->quoteDataFactory = $quoteDataFactory;

        $this->coreRegistry = $coreRegistry;

        parent::__construct($context);
    }

    /**
     * Execute action
     *
     * @return void
     */
    public function execute()
    {
        $quote = $this->checkoutSession->getQuote();

        $this->coreRegistry->register(RegistryConstants::CURRENT_STORE_ID, $quote->getStoreId());

        $quoteData = $this->quoteDataFactory->create()
            ->loadByQuote($quote)
            ->setToken('')
            ->save();

        return $this->pageFactory->create();
    }
}
