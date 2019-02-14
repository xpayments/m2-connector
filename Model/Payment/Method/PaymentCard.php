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
 * Saved Payment Card payment method
 */
class PaymentCard extends \CDev\XPaymentsConnector\Model\Payment\Method
{
    /**
     * Payment method code
     */
    const CODE = 'xpc_payment_card';

    /**
     * Payment method code
     */
    protected $_code = self::CODE;

    /**
     * Payment Form block
     */
    protected $_formBlockType = \CDev\XPaymentsConnector\Block\Adminhtml\PaymentCard\CardList::class;

    /**
     * Get ID of the current customer
     *
     * @return int
     */
    protected function getCustomerId()
    {
        return $this->isAdmin()
            ? $this->sessionQuote->getCustomerId()
            : $this->customerSession->getCustomer()->getId();
    }

    /**
     * Get Quote from session
     *
     * @return \Magento\Quote\Model\Quote
     */
    protected function getSessionQuote()
    {
        return $this->isAdmin()
            ? $this->sessionQuote->getQuote()
            : $this->checkoutSession->getQuote();
    }

    /**
     * Check if customer is logged in
     *
     * @return bool
     */
    protected function isCustomerLoggedIn()
    {
        return $this->isAdmin()
            ? true
            : $this->customerSession->isLoggedIn();
    }

    /**
     * Check if payment method is available
     *
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     *
     * @return bool
     */
    public function isAvailable(\Magento\Quote\Api\Data\CartInterface $quote = null)
    {
        if (
            parent::isAvailable($quote)
            && $this->isCustomerLoggedIn()
        ) {

            $result = $this->paymentCardFactory->create()->checkCustomerHasCards(
                $this->getCustomerId()
            );

        } else {

            $result = false;
        }

        return $result;
    }

    /**
     * Validate payment method information object
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     *
     * @return $this
     */
    public function validate()
    {
        parent::validate();

        $errorMessage = false;

        $logData = array();

        try {

            $info = $this->getInfoInstance();

            $additionalData = $info->getAdditionalInformation();

            $logData = array(
                'Customer ID: ' . var_export($this->getCustomerId(), true),
                'Card ID: ' . var_export($additionalData['card_id'], true),
            );

            $checkCard = $this->paymentCardFactory->create()->checkCardForCustomer(
                $this->getCustomerId(),
                $additionalData['card_id']
            );

            if (!$checkCard) {
                $errorMessage = __('Wrong payment card: %1', $additionalData['card_id']);
            }

        } catch (\Exception $exception) {

            $logData[] = 'Exception: ' . $exception->getMessage();

            $errorMessage = __('Error in payment card validation');
        }

        if ($errorMessage) {

            $logData = implode(PHP_EOL, $logData);

            $this->helper->logDebug($errorMessage->__toString(), $logData);

            throw new \Magento\Framework\Exception\LocalizedException($errorMessage);
        }

        return $this;
    }

    /**
     * Get callback URL
     *
     * @return string
     */
    protected function getCallbackUrl()
    {
        $params = array(
            'quote_id' => $this->getSessionQuote()->getId(),
        );

        return $this->urlBuilder->getUrl('xpc/processing/callback', $params);
    }

    /**
     * Authorize request
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
        $errorMessage = false;

        $this->paymentAction = $this->getInitPaymentActionName();

        try {

            $additionalData = $payment->getAdditionalInformation();

            $txnId = $this->paymentCardFactory
                ->create()->load($additionalData['card_id'])
                ->getData('txnId');

            $refId = $payment->getOrder()->getIncrementId();
            
            $description = 'Order #' . $refId;

            $preparedCart = $this->helper->cart->prepareCart($this->getSessionQuote(), $refId);

            $data = array(
                'txnId'       => $txnId,
                'refId'       => $refId,
                'amount'      => $amount,
                'description' => $description,
                'cart'        => $preparedCart,
                'callbackUrl' => $this->getCallbackUrl(),
            );

            $response = $this->helper->api->requestPaymentRecharge($data);

            if ($response->getStatus()) {

                $this->processGetInfo($payment, $response->getField('transaction_id'));

            } else {

                $errorMessage = __($response->getErrorMessage());
            }

        } catch (\Exception $exception) {

            $errorMessage = __('Error in authorization: ' . $exception->getMessage());
        }

        if ($errorMessage) {
            throw new \Magento\Framework\Exception\LocalizedException($errorMessage);
        }

        return $this;
    }
}
