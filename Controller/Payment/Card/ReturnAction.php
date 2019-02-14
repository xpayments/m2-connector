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
 * Add new payment card return action
 */
class ReturnAction extends \CDev\XPaymentsConnector\Controller\Payment\Card
{
    /**
     * Customer repository
     */
    protected $customerRepository = null;

    /**
     * Payment Card factory
     */
    protected $paymentCardFactory = null;

    /**
     * Page factory
     */
    protected $pageFactory = null;

    /**
     * XPC Helper
     */
    protected $helper = null;

    /**
     * Constructor
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Customer\Model\Session\Proxy $customerSession
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \CDev\XPaymentsConnector\Model\PaymentCardFactory $paymentCardFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \CDev\XPaymentsConnector\Helper\Data $helper
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Framework\View\Result\PageFactory $pageFactory
     *
     * @return void
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Customer\Model\Session\Proxy $customerSession,
        \Magento\Framework\Registry $coreRegistry,
        \CDev\XPaymentsConnector\Model\PaymentCardFactory $paymentCardFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \CDev\XPaymentsConnector\Helper\Data $helper,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Framework\View\Result\PageFactory $pageFactory
    ) {
        parent::__construct($context, $customerSession, $coreRegistry, $storeManager);

        $this->customerRepository = $customerRepository;
        $this->paymentCardFactory = $paymentCardFactory;
        $this->helper = $helper;
        $this->pageFactory = $pageFactory;
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
     * Execute request
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     */
    public function execute()
    {
        try {

            $customer = $this->getCustomer();
            $storeId = $this->initCurrentStoreId();

            $txnId = $this->getRequest()->getParam('txnId');

            $response = $this->helper->api->requestPaymentInfo($txnId, false, true);

            if (
                $response->getStatus()
                && 'Y' == $response->getField('payment')['saveCard']
            ) {

                $data = array(
                    'user_id'    => $this->customerId,
                    'txnId'      => $txnId,
                    'conf_id'    => $this->helper->settings->getXpcConfig('active_confid'),
                    'store_id'   => $storeId,
                    'address_id' => $customer->getDefaultBilling(),
                );

                $this->paymentCardFactory->create()
                    ->importFromResponse($response->getField('payment'))
                    ->addData($data)
                    ->save();

                // Due to redirect save result to session
                $this->customerSession->setIsPaymentCardSaved(true);

            } else {

                $this->customerSession->setIsPaymentCardSaved(false);
                $this->customerSession->setSavePaymentCardError('Payment Card was not saved');
            }

        } catch (\Exception $exception) {

            $this->customerSession->setIsPaymentCardSaved(false);
            $this->customerSession->setSavePaymentCardError($exception->getMessage());
        }

        return $this->pageFactory->create();
    }
}
