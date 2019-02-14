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

namespace CDev\XPaymentsConnector\Helper;

use Magento\Store\Model\ScopeInterface;

/**
 * Helper for cart
 */
class Cart extends \CDev\XPaymentsConnector\Helper\AbstractHelper
{
    /**
     * Result container
     */
    private $result = array();

    /**
     * Quote
     */
    private $quote = null;

    /**
     * Store info (something from config)
     */
    private $storeInfo = null;

    /**
     * Store manager (something from config)
     */
    private $storeManager = null;

    /**
     * Scope config
     */
    protected $scopeConfig = null;

    /**
     * Customer repository
     */
    protected $customerRepository = null;

    /**
     * Constructor
     *
     * @param \Magento\Store\Model\Information $storeInfo
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     *
     * @return void
     */
    public function __construct(
        \Magento\Store\Model\Information $storeInfo,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->storeInfo = $storeInfo;
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;

        $this->customerRepository = $customerRepository;
    }

    /**
     * Prepare items from quote for initial payment request
     *
     * @return void
     */
    protected function prepareItems()
    {
        $this->result['totalCost']    = $this->preparePrice($this->quote->getGrandTotal());
        $this->result['shippingCost'] = $this->preparePrice($this->quote->getShippingAddress()->getShippingAmount());
        $this->result['taxCost']      = $this->preparePrice($this->quote->getShippingAddress()->getTaxAmount());
        $this->result['discount']     = $this->preparePrice(abs($this->quote->getShippingAddress()->getDiscountAmount()));

        foreach ($this->quote->getAllVisibleItems() as $item) {

            $this->result['items'][] = array(
                'sku'      => $item->getData('sku'),
                'name'     => $item->getData('name'),
                'price'    => $this->preparePrice($item->getPrice()),
                'quantity' => (int)$item->getQty(),
            );

        }
    }

    /**
     * Get forced transaction type
     *
     * @return string
     */
    private function getForcedTransactionType()
    {
        return 'authorize' == $this->helper->settings->getPaymentConfig('payment_action')
            ? 'A'
            : 'S';
    }

    /**
     * Prepare cart for initial payment request
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param string $refId Reference to the order
     *
     * @return array
     */
    public function prepareCart(\Magento\Quote\Model\Quote $quote, $refId = false)
    {
        $this->quote = $quote;

        if ($refId) {
            $description = 'Order #' . $refId;
        } else {
            $description = 'Quote #' . $quote->getId();
        }

        $customer = $quote->getCustomer();

        if (
            !$customer->getEmail()
            || !$customer->getId()
        ) {
            $login = 'Anonymous customer (' . $description . ')';
        } else {
            $login = $customer->getEmail() . ' (User ID #' . $customer->getId() . ')';
        }

        $this->result = array(
            'login'                => $login,
            'billingAddress'       => $this->helper->address->prepareQuoteBillingAddress($this->quote),
            'shippingAddress'      => $this->helper->address->prepareQuoteShippingAddress($this->quote),
            'items'                => array(),
            'currency'             => $this->storeManager->getStore()->getCurrentCurrency()->getCode(),
            'shippingCost'         => 0.00,
            'taxCost'              => 0.00,
            'discount'             => 0.00,
            'totalCost'            => 0.00,
            'description'          => $description,
            'merchantEmail'        => $this->scopeConfig->getValue('trans_email/ident_support/email', ScopeInterface::SCOPE_STORE),
            'forceTransactionType' => $this->getForcedTransactionType($quote),
        );

        $this->prepareItems();

        return $this->result;
    }

    /**
     * Prepare cart for initial payment request
     *
     * @param Mage_Customer_Model_Customer $customer Customer
     *
     * @return array
     */
    public function prepareFakeCart($customerId)
    {
        $description = $this->helper->settings->getXpcConfig('zero_auth_description');

        $price = $this->preparePrice($this->helper->settings->getXpcConfig('zero_auth_amount'));

        $currency = $this->storeManager->getStore()->getCurrentCurrency()->getCode();

        $customer = $this->customerRepository->getById($customerId);

        $result = array(
            'login'                => $customer->getEmail() . ' (User ID #' . $customer->getId() . ')',
            'billingAddress'       => $this->helper->address->prepareCustomerBillingAddress($customer),
            'shippingAddress'      => $this->helper->address->prepareCustomerShippingAddress($customer),
            'items'                => array(
                array(
                    'sku'      => 'CardSetup',
                    'name'     => 'CardSetup',
                    'price'    => $price,
                    'quantity' => '1',
                ),
            ),
            'currency'             => $currency,
            'shippingCost'         => 0.00,
            'taxCost'              => 0.00,
            'discount'             => 0.00,
            'totalCost'            => $price,
            'description'          => $description,
            'merchantEmail'        => $this->scopeConfig->getValue('trans_email/ident_support/email', ScopeInterface::SCOPE_STORE),
            'forceTransactionType' => 'A',
        );

        return $result;
    }
}
