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
 * @category   XPay
 * @package    XPay_XPaymentsConnector
 * @copyright  (c) 2010-present Qualiteam software Ltd <info@x-cart.com>. All rights reserved
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace XPay\XPaymentsConnector\Controller\Processing;

/**
 * Return from payment form (in iframe)
 */
class ReturnAction extends \Magento\Framework\App\Action\Action
{
    /**
     * Result page factory
     */
    protected $resultPageFactory;

    /**
     * Controller result factory
     */
    protected $resultFactory = null;

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
     * @param \XPay\XPaymentsConnector\Helper\Data $helper
     * @param \XPay\XPaymentsConnector\Model\QuoteDataFactory $quoteFactory
     *
     * @return void
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Controller\ResultFactory $resultFactory,
        \Magento\Checkout\Model\Session $session,
        \XPay\XPaymentsConnector\Helper\Data $helper,
        \XPay\XPaymentsConnector\Model\QuoteDataFactory $quoteFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->resultFactory = $resultFactory;

        $this->helper = $helper;

        $this->quote = $session->getQuote();

        $this->quoteFactory = $quoteFactory;

        parent::__construct($context);
    }

    /**
     * Execute action
     *
     * @return void
     */
    public function execute()
    {
        $quoteData = $this->quoteFactory->create()->loadByConf(
            $this->quote->getId(),
            $this->helper->settings->getXpcConfig('active_confid')
        );

        $quoteData->setToken('')->save();

        // TODO: create template

        $contents = '<script>'
            . 'function postMessageToParent(msg) {'
            . '  if (window.JSON) {'
            . '    window.parent.postMessage(JSON.stringify(msg), \'*\');'
            . '  }'
            . '}'
            . 'postMessageToParent({message: "return"});'
            . '</script>';

        $resultRaw = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_RAW);
        $resultRaw->setContents($contents);

        return $resultRaw;
    }
}
