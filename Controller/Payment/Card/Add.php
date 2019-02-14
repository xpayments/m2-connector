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

namespace CDev\XPaymentsConnector\Controller\Payment\Card;

use CDev\XPaymentsConnector\Controller\RegistryConstants;

/**
 * Add Payment Card action
 */
class Add extends \CDev\XPaymentsConnector\Controller\Payment\Card
{
    /**
     * Customer repository
     */
    protected $customerRepository = null;

    /**
     * Page factory
     */
    protected $pageFactory = null;

    /**
     * XPC Helper
     */
    protected $helper = null;

    /**
     * URL Helper
     */
    protected $urlHelper = null;

    /**
     * Constructor
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Customer\Model\Session\Proxy $customerSession
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \CDev\XPaymentsConnector\Helper\Data $helper
     * @param \Magento\Framework\Url $urlHelper
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
        \CDev\XPaymentsConnector\Helper\Data $helper,
        \Magento\Framework\Url $urlHelper,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Framework\View\Result\PageFactory $pageFactory
    ) {
        parent::__construct($context, $customerSession, $coreRegistry, $storeManager);

        $this->customerRepository = $customerRepository;
        $this->helper = $helper;
        $this->urlHelper = $urlHelper;
        $this->pageFactory = $pageFactory;
    }

    /**
     * Obtain store model and check it
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     *
     * @return \Magento\Store\Model\Store
     */
    protected function getStore()
    {
        $this->storeId = $this->initCurrentStoreId();

        if (!$this->storeId) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Store is not set'));
        }

        if (!$this->helper->settings->getXpcConfig('zero_auth_active')) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Payment Card Setup is not allowed for this store'));
        }

        $store = $this->storeManager->getStore($this->storeId);

        return $store;
    }

    /**
     * Get Payment Token
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     *
     * @return string
     */
    protected function getToken()
    {
        $cart = $this->helper->cart->prepareFakeCart($this->customerId);

        $refId = 'authorization';

        $params = array(
            'user_id'  => $this->customerId,
            'store_id' => $this->storeId,
        );

        $callbackUrl = $this->urlHelper->getUrl('xpc/processing/callback', $params);
        $returnUrl = $this->urlHelper->getUrl('xpc/payment_card/return', $params);

        // Data to send to X-Payments
        $data = array(
            'confId'      => $this->helper->settings->getXpcConfig('active_confid'),
            'refId'       => $refId,
            'cart'        => $cart,
            'returnUrl'   => $returnUrl,
            'callbackUrl' => $callbackUrl,
        );

        $response = $this->helper->api->initPayment($data);

        if (!$response->getStatus()) {

            throw new \Magento\Framework\Exception\LocalizedException($response->getErrorPhrase());
        }

        return $response->getField('token');
    }

    /**
     * Obtain customer model and check it
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     *
     * @return \Magento\Customer\Model\Customer
     */
    protected function getCustomer()
    {
        $this->customerId = $this->customerSession->getCustomerId();

        if (!$this->customerId) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Customer is not set'));
        }

        $customer = $this->customerRepository->getById($this->customerId);

        if (!$customer->getDefaultBilling()) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Billing Address is not defined'));
        }

        return $customer;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return __(
            'We will authorize <strong>%1</strong> on your credit card in order to attach this credit card to your account. '
                . 'The amount will be released back to your card after a while.<br/>'
                . 'The transaction will be marked as <strong>%2</strong>.',
            $this->helper->settings->getXpcConfig('zero_auth_amount'),
            $this->helper->settings->getXpcConfig('zero_auth_description')
        );
    }

    /**
     * Execute request
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     */
    public function execute()
    {
        try {

            $customer = $this->getCustomer();
            $store = $this->getStore();

            $this->coreRegistry->register(RegistryConstants::ZERO_AUTH_TOKEN, $this->getToken());

            $this->messageManager->addWarning($this->getDescription());

        } catch (\Exception $exception) {

            $this->messageManager->addError(__($exception->getMessage()));
            
            $result = $this->_redirect('xpc/payment_card/index');
        }

        $result = $this->pageFactory->create();
        $result->getConfig()->getTitle()->set(__('New Payment Card'));

        return $result;
    }
}
