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

namespace CDev\XPaymentsConnector\Block\Adminhtml\PaymentCard;

/**
 * List of payment cards for the create order page
 */
class CardList extends \Magento\Payment\Block\Form
{
    /**
     * Session quote
     */
    protected $sessionQuote = null;

    /**
     * Payment Card Factory
     */
    protected $paymentCardFactory = null;

    /**
     * Constructor
     *
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Backend\Model\Session\Quote $sessionQuote
     * @param \CDev\XPaymentsConnector\Model\PaymentCardFactory $paymentCardFactory
     * @param array $data
     *
     * @return void
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Backend\Model\Session\Quote $sessionQuote,
        \CDev\XPaymentsConnector\Model\PaymentCardFactory $paymentCardFactory,
        array $data = array()
    ) {

        $this->sessionQuote = $sessionQuote;
        $this->paymentCardFactory = $paymentCardFactory;

        $this->_template = 'paymentcard/list.phtml';

        parent::__construct($context, $data);
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
            $this->sessionQuote->getCustomerId()
        );

        foreach ($collection as $card) {

            $cards[] = array(
                'card_id' => $card->getData('id'),
                'type'    => strtolower($card->getData('card_type')),
                'label'   => $card->getCardLabel(),
            );
        }

        return $cards;
    }
}
