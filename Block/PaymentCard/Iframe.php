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

use CDev\XPaymentsConnector\Controller\RegistryConstants;

/**
 * Add Payment Card iframe block
 */
class Iframe extends \Magento\Framework\View\Element\Template
{
    /**
     * Core registry
     */
    protected $coreRegistry = null;

    /**
     * XPC helper
     */
    protected $helper = null;

    /**
     * Constructor
     *
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     *
     * @return void
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \CDev\XPaymentsConnector\Helper\Data $helper,
        array $data = array()
    ) {
        $this->coreRegistry = $registry;
        $this->helper = $helper;

        $this->_template = 'paymentcard/iframe.phtml';

        parent::__construct($context, $data);
    }

    /**
     * Get Payment Token
     *
     * @return string
     */
    public function getToken()
    {
        return $this->coreRegistry->registry(RegistryConstants::ZERO_AUTH_TOKEN);
    }

    /**
     * Get form action URL
     *
     * @return string
     */
    public function getFormAction()
    {
        return $this->helper->settings->getPaymentUrl();
    }
}
