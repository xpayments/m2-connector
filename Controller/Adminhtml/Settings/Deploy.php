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
 * Deploy configuration bundle controller action
 */
class Deploy extends \CDev\XPaymentsConnector\Controller\Adminhtml\Settings
{
    /**
     * Check XPC bundle
     *
     * @throws \Magento\Framework\Magento\Framework\Exception
     *
     * @return string
     */
    protected function getBundle()
    {
        $bundle = $this->getRequest()->getParam('bundle');
        $bundle = (string)$bundle;

        $decoded = $this->helper->settings->decodeBundle($bundle);

        // Check submitted bundle
        if (empty($decoded)) {

            $this->helper->settings->setXpcConfig('is_configured', false, true);

            throw new \Magento\Framework\Exception\LocalizedException(__('Invalid configuration bundle'));
        }

        return $bundle;
    }

    /**
     * Load the page defined in view/adminhtml/layout/xpc_settings_index.xml
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $this->initCurrentStoreId();

        $redirect = $this->redirectToTab($this->helper->settings::TAB_CONNECTION);

        try {

            $this->helper->settings->cleanXpcBundle();

            // Check submitted bundle
            $bundle = $this->getBundle();

            // Save bundle and reload config
            $this->helper->settings->setXpcBundle($bundle);

            // Force recheck configuration
            $this->helper->settings->setRecheckFlag();

            if ($this->helper->settings->isConfigured()) {

                $this->importPaymentMethods();
                $this->autoActivateZeroAuth();

                $this->addSuccessTopMessage('Configuration bundle has been deployed successfully');

                $redirect = $this->redirectToTab($this->helper->settings::TAB_PAYMENT_METHODS);
            }

        } catch (\Exception $exception) {

            $this->addErrorTopMessage($exception->getMessage());
        }

        return $redirect;
    }
}
