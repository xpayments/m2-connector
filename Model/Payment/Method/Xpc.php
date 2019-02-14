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

namespace CDev\XPaymentsConnector\Model\Payment\Method;

/**
 * XPC payment method
 */
class Xpc extends \CDev\XPaymentsConnector\Model\Payment\Method
{
    /**
     * Payment method code
     */
    const CODE = 'xpc';

    /**
     * Payment method code
     */
    protected $_code = self::CODE;

    /**
     * Check if payment method is available
     *
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     *
     * @return bool
     */
    public function isAvailable(\Magento\Quote\Api\Data\CartInterface $quote = null)
    {
        return parent::isAvailable($quote)
            && !$this->isAdmin();
    }

    /**
     * Authorize request (actually check it via get_info)
     *
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @param string $amount
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     *
     * @return $this
     */
    public function authorize(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        $errorPhrase = false;

        $this->paymentAction = $this->getInitPaymentActionName();

        try {

            $txnId = $this->quoteDataFactory->create()
                ->loadByQuoteId($payment->getOrder()->getQuoteId())
                ->getTxnid();

            $this->processGetInfo($payment, $txnId);

        } catch (\Exception $exception) {

            $errorPhrase = __('Error in authorization: %1', $exception->getMessage());
        }

        if ($errorPhrase) {
            throw new \Magento\Framework\Exception\LocalizedException($errorPhrase);
        }

        return $this;
    }
}
