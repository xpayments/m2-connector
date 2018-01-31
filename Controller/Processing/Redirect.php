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
 * @category   Cdev
 * @package    Cdev_XPaymentsConnector
 * @copyright  (c) 2010-present Qualiteam software Ltd <info@x-cart.com>. All rights reserved
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Cdev\XPaymentsConnector\Controller\Processing;

/**
 * Redirect to the payment form (in iframe)
 */
class Redirect extends \Magento\Framework\App\Action\Action
{
    /**
     * Result page factory
     */
    protected $resultPageFactory = null;

    /**
     * Controller result factory
     */
    protected $resultFactory = null;

    /**
     * Quote factory
     */
    private $quoteFactory = null;

    /**
     * Helper
     */
    private $helper = null;

    /**
     * Quote
     */
    private $quote = null;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Controller\ResultFactory $resultFactory
     * @param \Magento\Checkout\Model\Session $session
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Cdev\XPaymentsConnector\Helper\Data $helper
     * @param \Cdev\XPaymentsConnector\Model\QuoteDataFactory $quoteFactory
     *
     * @return void
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Controller\ResultFactory $resultFactory,
        \Magento\Checkout\Model\Session $session,
        \Magento\Customer\Model\Session $customerSession,
        \Cdev\XPaymentsConnector\Helper\Data $helper,
        \Cdev\XPaymentsConnector\Model\QuoteDataFactory $quoteFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->resultFactory = $resultFactory;
        $this->helper = $helper;

        $this->quote = $session->getQuote();
        $this->session = $customerSession;

        $this->quoteFactory = $quoteFactory;

        parent::__construct($context);
    }

    /**
     * Get return URL
     *
     * @return string
     */
    private function getReturnUrl()
    {
        $params = array(
            'quote_id' => $this->quote->getId(),
            'conf_id'  => $this->helper->settings->getXpcConfig('active_confid'),
        );

        return $this->_url->getUrl('xpc/processing/return', $params);
    }

    /**
     * Get callback URL
     *
     * @return string
     */
    private function getCallbackUrl()
    {
        $params = array(
            'quote_id' => $this->quote->getId(),
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
        // Apparently this is not necesary, but just in case
        $this->quote->reserveOrderId()->save();

        $quoteData = $this->quoteFactory->create()->loadByConf(
            $this->quote->getId(),
            $this->helper->settings->getXpcConfig('active_confid')
        );

        if (
            empty($quoteData->getToken())
            || empty($quoteData->getTxnid())
            || $this->getRequest()->getParam('drop_token')
        ) {

            $cart = $this->helper->cart->prepareCart($this->quote);

            // Data to send to X-Payments
            $data = array(
                'confId'      => $this->helper->settings->getXpcConfig('active_confid'),
                'refId'       => 'Order #' . $this->quote->getReservedOrderId(),
                'cart'        => $cart,
                'returnUrl'   => $this->getReturnUrl(),
                'callbackUrl' => $this->getCallbackUrl(),
            );

            $response = $this->helper->api->initPayment($data);

            $this->session->setXpcTxnId($response->getField('txnId'));

            $quoteData->setQuoteId($this->quote->getId())
                ->setConfId($this->helper->settings->getXpcConfig('active_confid'))
                ->setToken($response->getField('token'))
                ->setTxnid($response->getField('txnId'))
                ->save();
        }

        $action = $this->helper->settings->getPaymentUrl();

        $contents = '<form id="xpc-form" action="' . $action . '" method="post">'
            . '<input type="hidden" name="action" value="start">'
            . '<input type="hidden" name="token" value="' . $quoteData->getToken() . '">'
            . '</form>'
            . '<script>document.getElementById("xpc-form").submit()</script>';

        $resultRaw = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_RAW);
        $resultRaw->setContents($contents);

        return $resultRaw;

        /* TODO: implement template
        $resultPage = $this->resultPageFactory->create(
            false, 
            array(
                'template' => 'Cdev_XPaymentsConnector::processing/blank.phtml',
                'form_action' => $action,
                'token' => $token,
            )
        );
        */
    }
}
