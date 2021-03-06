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

/**
 * Mass delete Payment Cards Action
 */
class MassDelete extends \CDev\XPaymentsConnector\Controller\Adminhtml\Payment\Card
{
    /**
     * Filter
     */
    protected $filter = null;

    /**
     * Payment Card collection Factory
     */
    protected $paymentCardCollectionFactory = null;

    /**
     * Constructor
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Ui\Component\MassAction\Filter $filter
     * @param \CDev\XPaymentsConnector\Model\ResourceModel\PaymentCard\CollectionFactory $paymentCardCollectionFactory
     *
     * @return void
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \CDev\XPaymentsConnector\Model\ResourceModel\PaymentCard\CollectionFactory $paymentCardCollectionFactory
    ) {
        parent::__construct($context, $coreRegistry);

        $this->filter = $filter;
        $this->paymentCardCollectionFactory = $paymentCardCollectionFactory;
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

        $collection = $this->filter->getCollection($this->paymentCardCollectionFactory->create());
        $collectionSize = $collection->getSize();

        $collection->walk('delete');

        $this->messageManager->addSuccess(__('A total of %1 element(s) have been deleted.', $collectionSize));

        $result = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        return $result->setPath(
            'xpc/payment_card/index',
            array(
                'user_id' => $customerId,
                'store'   => $storeId,
            )
        );
    }
}
