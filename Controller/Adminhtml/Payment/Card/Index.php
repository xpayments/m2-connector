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
 * List of Payment Cards
 */
class Index extends \CDev\XPaymentsConnector\Controller\Adminhtml\Payment\Card
{
    /**
     * Customer repository
     */
    protected $customerRepository = null;

    /**
     * Session model
     */
    protected $session = null;

    /**
     * Store manager
     */
    protected $storeManager = null;

    /**
     * Result page factory
     */
    protected $resultPageFactory = null;

    /**
     * Constructor
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Backend\Model\Session\Proxy $session
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     *
     * @return void
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Backend\Model\Session\Proxy $session,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        parent::__construct($context, $coreRegistry);

        $this->session = $session;

        $this->resultPageFactory = $resultPageFactory;
        $this->customerRepository = $customerRepository;

        $this->storeManager = $storeManager;
    }

    /**
     * Process top message with save card result
     *
     * @return void
     */
    protected function processTopMessage()
    {
        // Due to redirect Message Manager cannot be used directly

        if ($this->session->getIsPaymentCardSaved()) {

            $this->messageManager->addSuccess(__('Payment Card saved'));

        } elseif ($this->session->getSavePaymentCardError()) {

            $this->messageManager->addError(__($this->session->getSavePaymentCardError()));
        }

        $this->session->setIsPaymentCardSaved(null);
        $this->session->setSavePaymentCardError(null);
    }

    /**
     * Execute action
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $storeId = $this->initCurrentStoreId();
        $customerId = $this->initCurrentCustomer();

        $customerName = $storeName = '';

        if ($customerId) {

            $customer = $this->customerRepository->getById($customerId);

            $customerName = $customer->getFirstname() . ' ' . $customer->getLastname();
        }

        if ($storeId) {
            $storeName = $this->storeManager->getStore($storeId)->getName();
        }

        if ($customerName && $storeName) {

            $title = __('Payment Cards of %1 in %2', $customerName, $storeName);

        } elseif ($customerName) {

            $title = __('Payment Cards of %1', $customerName);

        } elseif ($storeName) {

            $title = __('Payment Cards in %1', $storeName);

        } else {

            $title = __('Payment Cards');
        }

        $this->processTopMessage();

        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend($title);

        return $resultPage;
    }
}
