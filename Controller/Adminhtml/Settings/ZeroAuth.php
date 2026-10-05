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
 * Update zero-auth (card setup) settings
 */
class ZeroAuth extends \CDev\XPaymentsConnector\Controller\Adminhtml\Settings
{
    /**
     * Esceute action
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $this->initCurrentStoreId();

        $redirect = $this->redirectToTab($this->helper->settings::TAB_ZERO_AUTH);

        try {

            $active = $this->getRequest()->getParam('zero_auth_active');

            $amount = $this->helper->settings->preparePrice(
                $this->getRequest()->getParam('zero_auth_amount')
            );

            $description = $this->getRequest()->getParam('zero_auth_description');

            $this->helper->settings->setXpcConfig('zero_auth_active', $active);
            $this->helper->settings->setXpcConfig('zero_auth_amount', $amount);
            $this->helper->settings->setXpcConfig('zero_auth_description', $description);

        } catch (\Exception $exception) {

            $this->addErrorTopMessage($exception->getMessage());
        }

        return $redirect;
    }
}
