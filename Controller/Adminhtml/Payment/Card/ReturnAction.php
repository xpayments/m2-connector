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

/**
 * Add new payment card return action
 */
class ReturnAction extends \CDev\XPaymentsConnector\Controller\Adminhtml\Payment\Card
{
    /**
     * Result page factory
     */
    protected $resultPageFactory = null;

    /**
     * XPC Helper
     */
    protected $helper = null;

    /**
     * Session model
     */
    protected $session = null;

    /**
     * Payment Card factory
     */
    protected $paymentCardFactory = null;

    /**
     * Customer repository
     */
    protected $customerRepository = null;

    /**
     * Constructor
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \CDev\XPaymentsConnector\Model\PaymentCardFactory $paymentCardFactory
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \CDev\XPaymentsConnector\Helper\Data $helper
     * @param \Magento\Backend\Model\Session\Proxy $session
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     *
     * @return void
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \CDev\XPaymentsConnector\Model\PaymentCardFactory $paymentCardFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \CDev\XPaymentsConnector\Helper\Data $helper,
        \Magento\Backend\Model\Session\Proxy $session,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        parent::__construct($context, $coreRegistry);

        $this->helper = $helper;
        $this->session = $session;

        $this->paymentCardFactory = $paymentCardFactory;
        $this->customerRepository = $customerRepository;

        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * Disable URL keys check for Return action
     *
     * @return bool
     */
    public function _processUrlKeys()
    {
        return true;
    }

    /**
     * Execute action
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        try {

            $storeId = $this->initCurrentStoreId();

            $customerId = $this->initCurrentCustomer();
            $customer = $this->customerRepository->getById($customerId);

            $txnId = $this->getRequest()->getParam('txnId');

            $response = $this->helper->api->requestPaymentInfo($txnId, false, true);

            if (
                $response->getStatus()
                && 'Y' == $response->getField('payment')['saveCard']
            ) {

                $data = array(
                    'user_id'    => $customerId,
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
                $this->session->setIsPaymentCardSaved(true);

            } else {

                $this->session->setIsPaymentCardSaved(false);
                $this->session->setSavePaymentCardError('Payment Card was not saved');
            }

        } catch (\Exception $exception) {

            $this->session->setIsPaymentCardSaved(false);
            $this->session->setSavePaymentCardError($exception->getMessage());
        }

        return $this->resultPageFactory->create();
    }
}
