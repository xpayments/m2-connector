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

namespace XPay\XPaymentsConnector\Helper;

/**
 * Helper for address
 */
class Address extends \XPay\XPaymentsConnector\Helper\AbstractHelper
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

            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $region = $objectManager->create('Magento\Directory\Model\Region')->load($data['region_id']);

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
     * @param $type Address type, Billing or Shipping
     *
     * @return array
     */
    private function prepareAddress(\Magento\Quote\Model\Quote $quote = null, $type = self::BILLING_ADDRESS)
    {
        $getAddress = 'get' . $type . 'Address';
        $getDefaultAddress = 'getDefault' . $type . 'Address';

        $customerAddress = $customerDefaultAddress = $quoteAddress = $orderAddress = array();

        if ($quote) {

            $customer = $quote->getCustomer();

            if ($quote->$getAddress()) {
                $quoteAddress = $quote->$getAddress()->getData();
            }
        }

        // TODO: implement customer's address

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
        return $this->prepareAddress($quote, self::BILLING_ADDRESS);
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
        return $this->prepareAddress($quote, self::SHIPPING_ADDRESS);
    }
}
