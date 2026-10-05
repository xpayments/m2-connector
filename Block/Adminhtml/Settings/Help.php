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

namespace CDev\XPaymentsConnector\Block\Adminhtml\Settings;

/**
 * X-Payments Connector help/contact us link
 */
class Help extends \Magento\Backend\Block\Template
{
    /**
     * Constructor
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param array $data
     *
     * @return void
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        array $data = array()
    ) {
        $this->_template = 'settings/help.phtml';

        parent::__construct($context, $data);
    }

    /**
     * Get Contact Us link
     *
     * @return string
     */
    public function getContactUsUrl()
    {
        return 'http://www.x-payments.com/contact-us.html?utm_source=mage_shop&utm_medium=link&utm_campaign=mage_shop_link';
    }
}
