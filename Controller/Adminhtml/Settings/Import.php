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
 * Import payment methods action
 */
class Import extends \CDev\XPaymentsConnector\Controller\Adminhtml\Settings
{
    /**
     * Load the page defined in view/adminhtml/layout/xpc_settings_index.xml
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $this->initCurrentStoreId();

        $redirect = $this->redirectToTab($this->helper->settings::TAB_PAYMENT_METHODS);

        try {

            if ($this->helper->settings->isConfigured()) {

                $this->importPaymentMethods();
                $this->autoActivateZeroAuth();
            }

        } catch (\Exception $e) {

            $this->addErrorTopMessage($e->getMessage());
        }

        return $redirect;
    }
}
