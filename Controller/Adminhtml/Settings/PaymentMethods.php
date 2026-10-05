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
 * Update payment methods controller action
 */
class PaymentMethods extends \CDev\XPaymentsConnector\Controller\Adminhtml\Settings
{
    /**
     * Execute action
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $this->initCurrentStoreId();

        $redirect = $this->redirectToTab($this->helper->settings::TAB_PAYMENT_METHODS);

        $mode = $this->getRequest()->getParam('mode');

        try {

            if ('update' == $mode) {

                $this->updatePaymentMethods();
                $this->addSuccessTopMessage('Payment methods updated successfully');

            } elseif ('import' == $mode) {

                $this->importPaymentMethods();
                $this->addSuccessTopMessage('Payment methods import successful');
            }

            $this->autoActivateZeroAuth();

        } catch (\Exception $exception) {

            $this->addErrorTopMessage($exception->getMessage());
        }

        return $redirect;
    }
}
