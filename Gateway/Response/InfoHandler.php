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

namespace XPay\XPaymentsConnector\Gateway\Response;

/**
 * Payment info handler
 */
class InfoHandler implements \Magento\Payment\Gateway\Response\HandlerInterface
{
    const TXN_ID = 'TXN_ID';

    /**
     * Handles response from payment info request
     *
     * @param array $handlingSubject
     * @param array $response
     *
     * @return void
     */
    public function handle(array $handlingSubject, array $response)
    {
        if (!isset($handlingSubject['payment'])
            || !$handlingSubject['payment'] instanceof \Magento\Payment\Gateway\Data\PaymentDataObjectInterface
        ) {
            throw new \InvalidArgumentException('Payment data object should be provided');
        }

        /** @var PaymentDataObjectInterface $paymentDO */
        $paymentDO = $handlingSubject['payment'];

        $payment = $paymentDO->getPayment();

        if (!empty($response['maskedCardData'])) {
            $maskedCard = $response['maskedCardData']['type'] . ' '
                . $response['maskedCardData']['first6'] . '******' . $response['maskedCardData']['last4']
                . ' (' . $response['maskedCardData']['expire_month'] . '/' .  $response['maskedCardData']['expire_year'] . ')';
        }

        $data = array(
            'Message' => $response['message'],
            'Credit Card' => $maskedCard,
        );

        /** @var $payment \Magento\Sales\Model\Order\Payment */
        $payment->setTransactionId($response[self::TXN_ID]);

        foreach ($data as $key => $value) {
            $payment->setAdditionalInformation($key, $value);
        }

        $payment->setIsTransactionClosed(false);
    }
}
