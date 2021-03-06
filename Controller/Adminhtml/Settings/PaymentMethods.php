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
