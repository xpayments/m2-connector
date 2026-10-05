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
 * https://opensource.org/license/OSL-3.0
 *
 * @author     X-Cart Holdings LLC <info@x-cart.com>
 * @category   CDev
 * @package    CDev_XPaymentsConnector
 * @copyright  (c) 2010-present X-Cart Holdings LLC <info@x-cart.com>. All rights reserved
 * @license    https://opensource.org/license/OSL-3.0  Open Software License (OSL 3.0)
 */

namespace CDev\XPaymentsConnector\Observer\PaymentCard;

use Magento\Payment\Model\InfoInterface;
use Magento\Quote\Api\Data\PaymentInterface;

/**
 * Data assign observer for Payment Card payment method
 */
class DataAssignObserver extends \Magento\Payment\Observer\AbstractDataAssignObserver
{
    /**
     * Assign data
     *
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $method = $this->readMethodArgument($observer);
        $data = $this->readDataArgument($observer);

        $additionalData = $data->getData(PaymentInterface::KEY_ADDITIONAL_DATA);

        if (!empty($additionalData['card_id'])) {
            $paymentInfo = $method->getInfoInstance();
            $paymentInfo->setAdditionalInformation('card_id', $additionalData['card_id']);
        }
    }
}
