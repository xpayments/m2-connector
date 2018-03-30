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

namespace CDev\XPaymentsConnector\Controller\Adminhtml\Settings;

/**
 * Update settings action
 */
class Update extends AbstractAction
{
    /**
     * Load the page defined in view/adminhtml/layout/xpc_settings_index.xml
     *
     * @return \Magento\Framework\Controller\Result
     */
    public function execute()
    {
        $redirect = $this->redirectToTab($this->helper->settings::TAB_CONNECTION);

        // Save IP address
        $ip = (string)$this->getRequest()->getParam('server_ip');
        $this->helper->settings->setXpcConfig('server_ip', $ip);

        // Save force HTTP option
        $forceHttp = (bool)$this->getRequest()->getParam('force_http');
        $this->helper->settings->setXpcConfig('force_http', $forceHttp);

        // Save title
        $title = (string)$this->getRequest()->getParam('title');
        $this->helper->settings->setPaymentConfig('title', $title);

        // Save payment action
        $paymentAction = (string)$this->getRequest()->getParam('payment_action');
        $this->helper->settings->setPaymentConfig('payment_action', $paymentAction);

        $this->helper->settings->flushCache();

        return $redirect;
    }
}
