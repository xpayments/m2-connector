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

namespace CDev\XPaymentsConnector\Ui\PaymentCard;

use Magento\Checkout\Model\ConfigProviderInterface;
use CDev\XPaymentsConnector\Model\Payment\Method\PaymentCard;

/**
 * Configuration provider for Payment Card method at checkout
 */
class ConfigProvider implements ConfigProviderInterface
{
    /**
     * Customer session
     */
    protected $customerSession = null;

    /**
     * Customer session
     */
    protected $checkoutSession = null;

    /**
     * Payment Card Factory
     */
    protected $paymentCardFactory = null;

    /**
     * Payment method object
     */
    protected $method = null;

    /**
     * Constructor
     *
     * @param \Magento\Payment\Helper\Data $paymentHelper
     * @param \Magento\Customer\Model\Session\Proxy $customerSession
     * @param \Magento\Checkout\Model\Session\Proxy $checkoutSession
     * @param \CDev\XPaymentsConnector\Model\PaymentCardFactory $paymentCardFactory
     *
     * @return void
     */
    public function __construct(
        \Magento\Payment\Helper\Data $paymentHelper,
        \Magento\Customer\Model\Session\Proxy $customerSession,
        \Magento\Checkout\Model\Session\Proxy $checkoutSession,
        \CDev\XPaymentsConnector\Model\PaymentCardFactory $paymentCardFactory
    ) {

        $this->customerSession = $customerSession;
        $this->checkoutSession = $checkoutSession;
        $this->paymentCardFactory = $paymentCardFactory;
        $this->method = $paymentHelper->getMethodInstance(PaymentCard::CODE);
    }

    /**
     * Get Config
     *
     * @return array
     */
    public function getConfig()
    {
        $config = array();

        if ($this->method->isAvailable($this->checkoutSession->getQuote())) {

            $config['payment'] = array(
                PaymentCard::CODE => array(
                    'payment_cards' => $this->getPaymentCards(),
                ),
            );
        }

        return $config;
    }

    /**
     * Get list of payment cards
     *
     * @return array
     */
    public function getPaymentCards()
    {
        $cards = array();

        $collection = $this->paymentCardFactory->create()->getCustomerCardsCollection(
            $this->customerSession->getCustomer()->getId()
        );

        foreach ($collection as $card) {

            $cards[] = array(
                'card_id' => $card->getData('id'),
                'type'    => strtolower($card->getData('card_type')),
                'label'   => $card->getCardLabel(),
                'dom_id'  => PaymentCard::CODE . '-' . $card->getData('id'),
            );
        }

        return $cards;
    }
}
