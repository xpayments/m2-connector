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

namespace CDev\XPaymentsConnector\Controller\Processing;

use CDev\XPaymentsConnector\Controller\RegistryConstants;

/**
 * Return from payment form (in iframe)
 */
class ReturnAction extends \Magento\Framework\App\Action\Action implements \Magento\Framework\App\CsrfAwareActionInterface
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
     * Create exception in case CSRF validation failed.
     * Return null if default exception will suffice.
     *
     * @param \Magento\Framework\App\RequestInterface $request
     *
     * @return InvalidRequestException|null
     */
    public function createCsrfValidationException(\Magento\Framework\App\RequestInterface $request): ?\Magento\Framework\App\Request\InvalidRequestException
    {
        return null;
    }

    /**
     * Perform custom request validation.
     * Return null if default validation is needed.
     *
     * @param \Magento\Framework\App\RequestInterface $request
     *
     * @return bool|null
     */
    public function validateForCsrf(\Magento\Framework\App\RequestInterface $request): ?bool
    {
        return true;
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
