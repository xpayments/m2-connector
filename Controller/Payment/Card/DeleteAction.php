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
 * Delete Payment Card
 */
class DeleteAction extends \CDev\XPaymentsConnector\Controller\Payment\Card
{
    /**
     * Payment Card factory
     */
    protected $paymentCardFactory = null;

    /**
     * Constructor
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Customer\Model\Session\Proxy $customerSession
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \CDev\XPaymentsConnector\Model\PaymentCardFactory $paymentCardFactory
     *
     * @return void
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Customer\Model\Session\Proxy $customerSession,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \CDev\XPaymentsConnector\Model\PaymentCardFactory $paymentCardFactory
    ) {

        parent::__construct($context, $customerSession, $coreRegistry, $storeManager);

        $this->paymentCardFactory = $paymentCardFactory;
    }

    /**
     * Execute request
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     */
    public function execute()
    {
        $this->initCurrentStoreId();

        $cardId = (int)$this->getRequest()->getParam('card_id');

        $checkCard = $this->paymentCardFactory->create()->checkCardForCustomer(
            $this->customerSession->getCustomer()->getId(),
            $cardId
        );

        if ($checkCard) {

            $this->paymentCardFactory->create()->load($cardId)->delete();

            $this->messageManager->addSuccessMessage('Payment Card deleted');

        } else {

            $this->messageManager->addErrorMessage('Wrong Card');
        }

        return $this->_redirect('xpc/payment_card/index');
    }
}
