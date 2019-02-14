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

namespace CDev\XPaymentsConnector\Model;

use CDev\XPaymentsConnector\Controller\RegistryConstants;

/**
 * Payment Card model
 */
class PaymentCard extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\DataObject\IdentityInterface
{
    /**
     * Cache tag
     */
    const CACHE_TAG = 'xpc_payment_card';

    /**
     * Cache tag
     */
    protected $_cacheTag = self::CACHE_TAG;

    /**
     * Event prefix
     */
    protected $_eventPrefix = 'xpc_payment_card';

    /**
     * Payment Card collection factory
     */
    protected $paymentCardCollectionFactory = null;

    /**
     * Core registry
     */
    protected $coreRegistry = null;

    /**
     * Constructor
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \CDev\XPaymentsConnector\Model\ResourceModel\PaymentCard\CollectionFactory $paymentCardCollectionFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     *
     * @return void
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \CDev\XPaymentsConnector\Model\ResourceModel\PaymentCard\CollectionFactory $paymentCardCollectionFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = array()
    ) {

        $this->paymentCardCollectionFactory = $paymentCardCollectionFactory;
        $this->coreRegistry = $coreRegistry;

        parent::__construct($context, $coreRegistry, $resource, $resourceCollection, $data);
    }

    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('CDev\XPaymentsConnector\Model\ResourceModel\PaymentCard');
    }

    /**
     * Get identities
     *
     * @return array
     */
    public function getIdentities()
    {
        return array(
            self::CACHE_TAG . '_' . $this->getId()
        );
    }

    /**
     * Get default values
     *
     * @return array
     */
    public function getDefaultValues()
    {
        $values = array();

        return $values;
    }

    /**
     * Get label for masked card
     *
     * @return string
     */
    public function getCardLabel()
    {
        return sprintf(
            '%s******%s (%s/%s)',
            $this->getData('first6'),
            $this->getData('last4'),
            $this->getData('expiration_month'),
            $this->getData('expiration_year')
        );
    }

    /**
     * Import masked card data from get_info response from X-Payments
     *
     * @param array $response
     * @param bool $graceful Throw exception or not
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     *
     * @return $this
     */
    public function importFromResponse($response, $graceful = false)
    {
        if (!empty($response['maskedCardData'])) {

            $cardData = $response['maskedCardData'];

            $data = array(
                'first6'           => $cardData['first6'],
                'last4'            => $cardData['last4'],
                'card_type'        => $cardData['type'],
                'expiration_month' => $cardData['expire_month'],
                'expiration_year'  => $cardData['expire_year'],
            );

            $this->setData($data);

        } elseif (!$graceful) {

            throw new \Magento\Framework\Exception\LocalizedException(__('Masked Card data is empty'));
        }

        return $this;
    }

    /**
     * Get payment cards collection for specific customer
     *
     * @param int $customerId Customer ID
     *
     * @return \CDev\XPaymentsConnector\Model\ResourceModel\PaymentCard\Collection
     */
    public function getCustomerCardsCollection($customerId)
    {
        $storeId = (int)$this->coreRegistry->registry(RegistryConstants::CURRENT_STORE_ID);

        $collection = $this->paymentCardCollectionFactory->create();

        $collection->addFieldToFilter('user_id', $customerId);
        $collection->addFieldToFilter('store_id', $storeId);

        return $collection;
    }

    /**
     * Check if customer has saved payment cards
     *
     * @param int $customerId Customer ID
     *
     * @return bool
     */
    public function checkCustomerHasCards($customerId)
    {
        return count($this->getCustomerCardsCollection($customerId)) > 0;
    }

    /**
     * Check that payment card belongs to customer
     *
     * @param int $customerId Customer ID
     * @param int $cardId Card ID
     *
     * @return bool
     */
    public function checkCardForCustomer($customerId, $cardId)
    {
        $this->load($cardId);

        return is_numeric($customerId)
            && (int)$customerId === (int)$this->getData('user_id');
    }
}
