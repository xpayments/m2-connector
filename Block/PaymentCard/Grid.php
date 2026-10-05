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

namespace CDev\XPaymentsConnector\Block\PaymentCard;

/**
 * Payment Card list
 */
class Grid extends \Magento\Framework\View\Element\Template
{
    /**
     * Customer session
     */
    protected $customerSession = null;

    /**
     * Payment Card factory
     */
    protected $paymentCardFactory = null;

    /**
     * Cards hash
     */
    protected $cards = null;

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\Session\Proxy $customerSession
     * @param \CDev\XPaymentsConnector\Model\PaymentCardFactory $paymentCardFactory
     * @param array $data
     *
     * @return void
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session\Proxy $customerSession,
        \CDev\XPaymentsConnector\Model\PaymentCardFactory $paymentCardFactory,
        array $data = array()
    ) {
        parent::__construct($context, $data);

        $this->_template = 'paymentcard/grid.phtml';

        $this->customerSession = $customerSession;
        $this->paymentCardFactory = $paymentCardFactory;
    }

    /**
     * Check if customer has payment cards
     *
     * @return bool
     */
    public function hasPaymentCards()
    {
        return !empty($this->getPaymentCards());
    }

    /**
     * Get list of payment cards
     *
     * @return array
     */
    public function getPaymentCards()
    {
        if (null !== $this->cards) {
            return $this->cards;
        }

        $this->cards = array();

        $collection = $this->paymentCardFactory->create()->getCustomerCardsCollection(
            $this->customerSession->getCustomer()->getId()
        );

        foreach ($collection as $card) {

            $params = array(
                'card_id' => $card->getData('id'),
            );

            $url = $this->getUrl('*/*/deleteAction', $params);

            $this->cards[] = array(
                'card_id'    => $card->getData('id'),
                'type'       => strtolower($card->getData('card_type')),
                'label'      => $card->getCardLabel(),
                'delete_url' => $url,
            );
        }

        return $this->cards;
    }

    /**
     * Link to the add payment card page
     *
     * @return string
     */
    public function getAddPaymentCardLink()
    {
        return $this->getUrl('*/*/add');
    }
}
