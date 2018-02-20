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
 * @category   XPay
 * @package    XPay_XPaymentsConnector
 * @copyright  (c) 2010-present Qualiteam software Ltd <info@x-cart.com>. All rights reserved
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace XPay\XPaymentsConnector\Controller\Adminhtml\Settings;

/**
 * Deploy configuration bundle controller action
 */
class Deploy extends AbstractAction
{
    /**
     * Some error messages
     */
    const ERROR_EMPTY_IMPORTED_METHODS = 'Payment methods import failed. Make sure you’ve activated your payment configurations and assigned them to this store in X-Payments dashboard.';
    const ERROR_INVALID_BUNDLE = 'Invalid configuration bundle';

    /**
     * Load the page defined in view/adminhtml/layout/xpc_settings_index.xml
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $redirect = $this->redirectToTab($this->helper->settings::TAB_CONNECTION);

        try {

            // Save force HTTP option
            $forceHttp = (bool)$this->getRequest()->getParam('force_http');
            $this->helper->settings->setXpcConfig('force_http', $forceHttp);

            $this->helper->settings->cleanXpcBundle();

            $bundle = $this->getRequest()->getParam('bundle');
            $bundle = (string)$bundle;

            $decoded = $this->helper->settings->decodeBundle($bundle);

            // Check submitted bundle
            if (empty($decoded)) {
                $this->helper->settings->setXpcConfig('is_configured', false, true);
                throw new \Exception(self::ERROR_INVALID_BUNDLE);
            }

            // Save bundle and reload config
            $this->helper->settings->setXpcBundle($bundle);

            // Force recheck configuration
            $this->helper->settings->setRecheckFlag();

            if ($this->helper->settings->isConfigured()) {

                $this->importPaymentMethods();
                // TODO: implement
                // $this->autoActivateZeroAuth();
                $this->addSuccessTopMessage('Configuration bundle has been deployed successfully');

                $redirect = $this->redirectToTab($this->helper->settings::TAB_PAYMENT_METHODS);
            }

        } catch (\Exception $e) {

            $this->addErrorTopMessage($e->getMessage());
        }

        return $redirect;
    }
}
