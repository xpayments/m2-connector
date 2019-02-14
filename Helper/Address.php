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

/**
 * Helper for address
 */
class Address extends \CDev\XPaymentsConnector\Helper\AbstractHelper
{
    /**
     * Placeholder for empty email (something which will pass X-Payments validation)
     */
    const EMPTY_USER_EMAIL = 'user@example.com';

    /**
     * Placeholder for not available cart data
     */
    const NOT_AVAILABLE = 'N/A';

    /**
     * Billing and shipping address names
     */
    const BILLING_ADDRESS = 'Billing';
    const SHIPPING_ADDRESS = 'Shipping';

    /**
     * Address Repository
     */
    protected $addressRepository = null;

    /**
     * Region factory
     */
    protected $regionFactory = null;

    /**
     * Constructor
     *
     * @param \Magento\Customer\Api\AddressRepositoryInterface $addressRepository
     *
     * @return void
     */
    public function __construct(
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository,
        \Magento\Directory\Model\RegionFactory $regionFactory
    ) {

        $this->addressRepository = $addressRepository;
        $this->regionFactory = $regionFactory;
    }

    /**
     * Prepare state
     *
     * @param array $data Address data
     *
     * @return string
     */
    private function prepareState($data)
    {
        $state = self::NOT_AVAILABLE;

        if (!empty($data['region_id'])) {

            $region = $this->regionFactory->create()->load($data['region_id']);

            if (
                $region
                && $region->getCode()
            ) {
                $state = $region->getCode();
            }
        }

        return $state;
    }

    /**
     * Prepare street (Address lines 1 and 2)
     *
     * @param array $data Address data
     *
     * @return string
     */
    private function prepareStreet($data)
    {
        $street = self::NOT_AVAILABLE;

        if (!empty($data['street'])) {

            $street = $data['street'];

            if (is_array($street)) {
                $street = array_filter($street);
                $street = implode("\n", $street);
            }
        }

        return $street;
    }

    /**
     * Prepare address for initial payment request (internal)
     *
     * @param \Magento\Quote\Model\Quote $quote Quote
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer Customer
     * @param $type Address type, Billing or Shipping
     *
     * @return array
     */
    protected function prepareAddress(\Magento\Quote\Model\Quote $quote = null, \Magento\Customer\Api\Data\CustomerInterface $customer = null, $type = self::BILLING_ADDRESS)
    {
        $getAddress = 'get' . $type . 'Address';
        $getDefaultAddressId = 'getDefault' . $type;

        $customerAddress = $customerDefaultAddress = $quoteAddress = $orderAddress = array();

        if ($quote) {

            $customer = $quote->getCustomer();

            if ($quote->$getAddress()) {
                $quoteAddress = $quote->$getAddress()->getData();
            }
        }

        if ($customer) {

            $addressId = $customer->$getDefaultAddressId();

            if ($addressId) {

                $customerDefaultAddress = $this->addressRepository->getById($addressId);

                $customerDefaultAddress = $customerDefaultAddress->__toArray();
            }
        }

        $data = array_merge(
            array_filter($customerAddress),
            array_filter($customerDefaultAddress),
            array_filter($quoteAddress),
            array_filter($orderAddress)
        );

        $result = array(
            'firstname' => !empty($data['firstname']) ? $data['firstname'] : self::NOT_AVAILABLE,
            'lastname'  => !empty($data['lastname']) ? $data['lastname'] : self::NOT_AVAILABLE,
            'address'   => $this->prepareStreet($data),
            'city'      => !empty($data['city']) ? $data['city'] : self::NOT_AVAILABLE,
            'state'     => $this->prepareState($data),
            'country'   => !empty($data['country_id']) ? $data['country_id'] : 'XX', // WA fix for MySQL 5.7 with strict mode
            'zipcode'   => !empty($data['postcode']) ? $data['postcode'] : self::NOT_AVAILABLE,
            'phone'     => !empty($data['telephone']) ? $data['telephone'] : '',
            'fax'       => '',
            'company'   => '',
            'email'     => !empty($data['email']) ? $data['email'] : self::EMPTY_USER_EMAIL,
        );

        return $result;
    }

    /**
     * Prepare billing address from quote for initial payment request
     *
     * @param \Magento\Quote\Model\Quote $quote Quote
     *
     * @return array
     */
    public function prepareQuoteBillingAddress(\Magento\Quote\Model\Quote $quote)
    {
        return $this->prepareAddress($quote, null, self::BILLING_ADDRESS);
    }

    /**
     * Prepare shipping address from quote for initial payment request
     *
     * @param \Magento\Quote\Model\Quote $quote Quote
     *
     * @return array
     */
    public function prepareQuoteShippingAddress(\Magento\Quote\Model\Quote $quote)
    {
        return $this->prepareAddress($quote, null, self::SHIPPING_ADDRESS);
    }

    /**
     * Prepare billing address from customer for initial payment request
     *
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     *
     * @return array
     */
    public function prepareCustomerBillingAddress(\Magento\Customer\Api\Data\CustomerInterface $customer)
    {
        return $this->prepareAddress(null, $customer, self::BILLING_ADDRESS);
    }

    /**
     * Prepare shipping address from customer for initial payment request
     *
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     *
     * @return array
     */
    public function prepareCustomerShippingAddress(\Magento\Customer\Api\Data\CustomerInterface $customer)
    {
        return $this->prepareAddress(null, $customer, self::SHIPPING_ADDRESS);
    }
}
