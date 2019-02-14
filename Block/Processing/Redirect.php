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

namespace CDev\XPaymentsConnector\Block\Processing;

/**
 * Redirect to payment form
 */
class Redirect extends \Magento\Framework\View\Element\Template
{
    /**
     * Session models
     */
    protected $customerSession = null;
    protected $checkoutSession = null;

    /**
     * XPC Quote Data factory
     */
    protected $quoteDataFactory = null;

    /**
     * XPC Helper
     */
    protected $helper = null;

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Checkout\Model\Session\Proxy $checkoutSession
     * @param \Magento\Customer\Model\Session\Proxy $customerSession
     * @param \CDev\XPaymentsConnector\Model\QuoteDataFactory $quoteDataFactory
     * @param \CDev\XPaymentsConnector\Helper\Data $helper
     * @param array $data
     *
     * @return void
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Checkout\Model\Session\Proxy $checkoutSession,
        \Magento\Customer\Model\Session\Proxy $customerSession,
        \CDev\XPaymentsConnector\Model\QuoteDataFactory $quoteDataFactory,
        \CDev\XPaymentsConnector\Helper\Data $helper,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->_template = 'processing/redirect.phtml';

        $this->customerSession = $customerSession;
        $this->checkoutSession = $checkoutSession;
        $this->quoteDataFactory = $quoteDataFactory;
        $this->helper = $helper;
    }

    /**
     * Get form action
     *
     * @return string
     */
    public function getFormAction()
    {
        return $this->helper->settings->getPaymentUrl();
    }

    /**
     * Get payment token
     *
     * @return string
     */
    public function getPaymentToken()
    {
        // TODO: Rework to use core registry

        $quoteData = $this->quoteDataFactory->create()->loadByQuote(
            $this->checkoutSession->getQuote()
        );

        return $quoteData->getToken();
    }

    /**
     * Get allows_save_card flag
     *
     * @return string
     */
    public function getAllowSaveCard()
    {
        if (
            $this->customerSession->isLoggedIn()
            && $this->helper->settings->getXpcConfig('can_save_cards')
        ) {

            $result = 'O';

        } else {

            $result = 'N';
        }

        return $result;
    }
}
