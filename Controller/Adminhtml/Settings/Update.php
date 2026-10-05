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

namespace CDev\XPaymentsConnector\Controller\Adminhtml\Settings;

/**
 * Update settings action
 */
class Update extends \CDev\XPaymentsConnector\Controller\Adminhtml\Settings
{
    /**
     * Load the page defined in view/adminhtml/layout/xpc_settings_index.xml
     *
     * @return \Magento\Framework\Controller\Result
     */
    public function execute()
    {
        $this->initCurrentStoreId();

        $redirect = $this->redirectToTab($this->helper->settings::TAB_CONNECTION);

        try {

            // Force recheck configuration
            $this->helper->settings->setRecheckFlag();

            if ($this->helper->settings->isConfigured()) {

                $this->addSuccessTopMessage('Configuration updated');
            }

        } catch (\Exception $exception) {

            $this->addErrorTopMessage($exception->getMessage());
        }

        return $redirect;
    }
}
