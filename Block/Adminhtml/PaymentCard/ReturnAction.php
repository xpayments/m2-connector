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

namespace CDev\XPaymentsConnector\Block\Adminhtml\PaymentCard;

use CDev\XPaymentsConnector\Controller\RegistryConstants;

/**
 * Add Payment Card return block
 */
class ReturnAction extends \Magento\Backend\Block\Template
{
    /**
     * Core registry
     */
    protected $coreRegistry = null;

    /**
     * Constructor
     *
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param array $data
     *
     * @return void
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        array $data = array()
    ) {
        $this->coreRegistry = $coreRegistry;

        $this->_template = 'paymentcard/return.phtml';

        parent::__construct($context, $data);
    }

    /**
     * Get redirect URL to the list of payment cards
     *
     * @return void
     */
    public function getRedirectUrl()
    {
        $params = array(
            'user_id' => $this->coreRegistry->registry(RegistryConstants::CURRENT_CUSTOMER_ID),
            'store'   => $this->coreRegistry->registry(RegistryConstants::CURRENT_STORE_ID),
        );

        return $this->getUrl('xpc/payment_card/index', $params);
    }
}
