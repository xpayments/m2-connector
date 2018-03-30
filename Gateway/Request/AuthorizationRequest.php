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

namespace CDev\XPaymentsConnector\Gateway\Request;

/**
 * Authorization request. Actually payment info reqquest.
 */
class AuthorizationRequest implements \Magento\Payment\Gateway\Request\BuilderInterface
{
    /**
     * Helper
     */
    private $helper = null;

    /**
     * Constructor
     *
     * @param \CDev\XPaymentsConnector\Helper\Data $helper
     *
     * @return void
     */
    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \CDev\XPaymentsConnector\Helper\Data $helper
    ) {
        $this->session = $customerSession;

        $this->helper = $helper;
    }

    /**
     * Builds ENV request
     *
     * @param array $buildSubject
     *
     * @return array
     */
    public function build(array $buildSubject)
    {
        if (!isset($buildSubject['payment'])
            || !$buildSubject['payment'] instanceof \Magento\Payment\Gateway\Data\PaymentDataObjectInterface
        ) {
            throw new \InvalidArgumentException('Payment data object should be provided');
        }

        $payment = $buildSubject['payment'];
        $order = $payment->getOrder();

        // Communicate with X-Payments
        // TODO: This should not be here actually
        // TODO: Take from quote, not session

        $response = $this->helper->api->requestPaymentInfo($this->session->getXpcTxnId(), false, true);
        $response = $response->getField('payment');

        $response['TXN_ID'] = $this->session->getXpcTxnId();

        return $response;
    }
}
