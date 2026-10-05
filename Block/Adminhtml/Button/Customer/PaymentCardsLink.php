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

namespace CDev\XPaymentsConnector\Block\Adminhtml\Button\Customer;

use Magento\Customer\Controller\RegistryConstants;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Magento\Framework\UrlInterface;

/**
 * Link for the customer's payment cards
 */
class PaymentCardsLink implements ButtonProviderInterface
{

    /**
     * URL Builder interface
     */
    protected $urlBuider = null;

    /**
     * Core registry
     */
    protected $coreRegistry = null;

    /**
     * Constructor
     *
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Magento\Framework\Registry $coreRegistry
     *
     * @return void
     */
    public function __construct(
        UrlInterface $urlBuilder,
        \Magento\Framework\Registry $coreRegistry
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->coreRegistry = $coreRegistry;
    }

    /**
     * Get button data
     *
     * @return array
     */
    public function getButtonData()
    {
        $params = array(
            'user_id' => $this->coreRegistry->registry(RegistryConstants::CURRENT_CUSTOMER_ID),
        );

        $url = $this->urlBuilder->getUrl('xpc/payment_card/index', $params);

        $data = array(
            'label'      => __('Payment Cards'),
            'on_click'   => sprintf('location.href = "%s";', $url),
            'class'      => 'add',
            'sort_order' => 40,
        );

        return $data;
    }
}
