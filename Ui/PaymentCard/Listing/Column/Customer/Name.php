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

namespace CDev\XPaymentsConnector\Ui\PaymentCard\Listing\Column\Customer;

/**
 * "Customer Name" column for payment cards grid
 */
class Name extends \CDev\XPaymentsConnector\Ui\PaymentCard\Listing\Column\Customer
{
    /**
     * Prepare data source, set customer name
     *
     * @param array $dataSource Data source
     *
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {

            foreach ($dataSource['data']['items'] as &$item) {

                $item['customer_name'] = $item['firstname'] . ' ' . $item['lastname'];

                $item['customer_url'] = $this->urlBuilder->getUrl('customer/index/edit', array('id' => $item['user_id']));
            }
        }

        return $dataSource;
    }
}
