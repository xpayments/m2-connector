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

namespace CDev\XPaymentsConnector\Controller\Adminhtml\Payment\Card;

use Magento\Framework\Controller\ResultFactory;
use CDev\XPaymentsConnector\Controller\RegistryConstants;

/**
 * Add New Payment Card action
 */
class Add extends \CDev\XPaymentsConnector\Controller\Adminhtml\Payment\Card
{
    /**
     * Customer repository
     */
    protected $customerRepository = null;

    /**
     * Result page factory
     */
    protected $resultPageFactory = null;

    /**
     * XPC Helper
     */
    protected $helper = null;

    /**
     * URL Helper
     */
    protected $urlHelper = null;

    /**
     * Current customer ID
     */
    protected $customerId = null;

    /**
     * Current Store ID
     */
    protected $storeId = null;

    /**
     * Store manager
     */
    protected $storeManager = null;

    /**
     * Constructor
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \CDev\XPaymentsConnector\Helper\Data $helper
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     *
     * @return void
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \CDev\XPaymentsConnector\Helper\Data $helper,
        \Magento\Framework\Url $urlHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {

        parent::__construct($context, $coreRegistry);

        $this->resultPageFactory = $resultPageFactory;

        $this->customerRepository = $customerRepository;
        $this->helper = $helper;
        $this->urlHelper = $urlHelper;

        $this->storeManager = $storeManager;
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
        $this->customerId = $this->initCurrentCustomer();

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

        $refId = $this->helper->settings->getXpcConfig('zero_auth_description');

        $params = array(
            'user_id'  => $this->customerId,
            'store_id' => $this->storeId,
        );

        // This is frontend URL
        $callbackUrl = $this->urlHelper->getUrl('xpc/processing/callback', $params);

        // This is backend URL
        $returnUrl = $this->getUrl('xpc/payment_card/return', $params);

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
     * Execute action
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        try {

            $customer = $this->getCustomer();
            $store = $this->getStore();

            $title = __('New Payment Card for %1 %2 in %3', $customer->getFirstname(), $customer->getLastname(), $store->getName());

            $this->coreRegistry->register(RegistryConstants::ZERO_AUTH_TOKEN, $this->getToken());

            $result = $this->resultPageFactory->create();
            $result->getConfig()->getTitle()->prepend($title);

        } catch (\Exception $exception) {

            $this->messageManager->addError(__($exception->getMessage()));

            $result = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

            $params = array(
                'user_id' => (int)$this->customerId,
            );

            $result->setPath('xpc/payment_card/index', $params);
        }

        return $result;
    }
}
