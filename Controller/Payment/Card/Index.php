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

/**
 * List of Payment Cards
 */
class Index extends \CDev\XPaymentsConnector\Controller\Payment\Card
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
     * Process top message with save card result
     *
     * @return void
     */
    protected function processTopMessage()
    {
        // Due to redirect Message Manager cannot be used directly

        if ($this->customerSession->getIsPaymentCardSaved()) {

            $this->messageManager->addSuccess(__('Payment Card saved'));

        } elseif ($this->customerSession->getSavePaymentCardError()) {

            $this->messageManager->addError(__($this->customerSession->getSavePaymentCardError()));
        }

        $this->customerSession->setIsPaymentCardSaved(null);
        $this->customerSession->setSavePaymentCardError(null);
    }

    /**
     * Execute request
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     */
    public function execute()
    {
        $this->initCurrentStoreId();

        $this->processTopMessage();

        $resultPage = $this->pageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('Saved Payment Cards'));

        return $resultPage;
    }
}
