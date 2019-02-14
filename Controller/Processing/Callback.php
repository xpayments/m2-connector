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
 * Callback processing
 */
class Callback extends \Magento\Framework\App\Action\Action
{
    /**
     * Result page factory
     */
    protected $resultPageFactory;

    /*
     * Controller result factory
     */
    protected $resultFactory = null;

    /**
     * XPC Helper
     */
    protected $helper = null;

    /**
     * Core coreRegistry
     */
    protected $coreRegistry = null;

    /**
     * Quote factory
     */
    private $quoteFactory = null;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Controller\ResultFactory $resultFactory
     * @param \Magento\Quote\Model\QuoteFactory $quoteFactory
     * @param \CDev\XPaymentsConnector\Helper\Data $helper
     *
     * @return void
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Controller\ResultFactory $resultFactory,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \CDev\XPaymentsConnector\Helper\Data $helper,
        \Magento\Framework\Registry $coreRegistry
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->resultFactory = $resultFactory;

        $this->helper = $helper;

        $this->quoteFactory = $quoteFactory;

        parent::__construct($context);

        $this->coreRegistry = $coreRegistry;
    }

    /**
     * Execute action
     *
     * @return void
     */
    public function execute()
    {
        $request = $this->getRequest()->getPostValue();

        $contents = '';

        if ('check_cart' == $this->getRequest()->getParam('action')) {

            $quoteId = (int)$this->getRequest()->getParam('quote_id');
            $storeId = (int)$this->getRequest()->getParam('store_id');
            $quote = $this->quoteFactory->create()->load($quoteId);

            if (!$storeId && $quote->getStoreId()) {
                $storeId = $quote->getStoreId();
            }

            $this->coreRegistry->register(RegistryConstants::CURRENT_STORE_ID, $storeId);

            if ($quote->getId()) {

                $cart = $this->helper->cart->prepareCart($quote);

                $data = array(
                    'status' => 'cart-changed',
                    'ref_id' => 'Order #' . $quote->getReservedOrderId(),
                    'cart'   => $cart,
                );

            } else {

                $data = array(
                    'status' => 'cart-not-changed',
                );
            }

            $this->helper->logInfo('Response for check-cart request', $data);

            $xml = $this->helper->api->convertHash2XML($data);
            $contents = $this->helper->api->encrypt($xml);
        }

        $resultRaw = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_RAW);
        $resultRaw->setContents($contents);

        return $resultRaw;
    }
}
