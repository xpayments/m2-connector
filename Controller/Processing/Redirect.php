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
 * Redirect to the payment form (in iframe)
 */
class Redirect extends \Magento\Framework\App\Action\Action
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
     * Get return URL
     *
     * @return string
     */
    protected function getReturnUrl()
    {
        $params = array(
            'quote_id' => $this->checkoutSession->getQuote()->getId(),
            'conf_id'  => $this->helper->settings->getXpcConfig('active_confid'),
        );

        return $this->_url->getUrl('xpc/processing/return', $params);
    }

    /**
     * Get callback URL
     *
     * @return string
     */
    protected function getCallbackUrl()
    {
        $params = array(
            'quote_id' => $this->checkoutSession->getQuote()->getId(),
            'conf_id'  => $this->helper->settings->getXpcConfig('active_confid'),
        );

        return $this->_url->getUrl('xpc/processing/callback', $params);
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

        // Apparently this is not necesary, but just in case
        $quote->reserveOrderId()->save();

        $quoteData = $this->quoteDataFactory->create()->loadByQuote($quote);

        if (
            !$quoteData->isValid()
            || $this->getRequest()->getParam('drop_token')
        ) {

            $cart = $this->helper->cart->prepareCart($quote);

            // Data to send to X-Payments
            $data = array(
                'confId'      => $this->helper->settings->getXpcConfig('active_confid'),
                'refId'       => 'Order #' . $quote->getReservedOrderId(),
                'cart'        => $cart,
                'returnUrl'   => $this->getReturnUrl(),
                'callbackUrl' => $this->getCallbackUrl(),
            );

            $response = $this->helper->api->initPayment($data);

            $quoteData->setQuoteId($quote->getId())
                ->setConfId($this->helper->settings->getXpcConfig('active_confid'))
                ->setToken($response->getField('token'))
                ->setTxnid($response->getField('txnId'))
                ->setExpiration()
                ->save();
        }

        return $this->pageFactory->create();
    }
}
