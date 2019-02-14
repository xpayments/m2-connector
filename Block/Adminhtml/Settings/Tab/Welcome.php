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

namespace CDev\XPaymentsConnector\Block\Adminhtml\Settings\Tab;

/**
 * X-Payments Connector Welcome tab
 */
class Welcome extends \CDev\XPaymentsConnector\Block\Adminhtml\Settings\Tab
{
    /**
     * Current tab
     */
    protected $tab = \CDev\XPaymentsConnector\Helper\Settings::TAB_WELCOME;

    /**
     * Determines whether to display the tab
     * Add logic here to decide whether you want the tab to display
     *
     * @return bool
     */
    public function canShowTab()
    {
        return $this->isWelcomeMode();
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        $description = 'Give your customers – and yourself – peace of mind with this payment processing module
            that guarantees compliance with PCI security mandates, significantly reduces the risk of
            data breaches and ensures you won’t be hit with a fine of up to $500,000 for non-compliance.
            Safely and conveniently store customers credit card information to use for new orders, reorders
            or recurring payments.';

        return __($description);
    }

    /**
     * Get System/X-Payments connector link
     *
     * @return string
     */
    public function getTrialDemoUrl()
    {
        return 'http://www.x-payments.com/trial-demo.html?utm_source=mage_shop&utm_medium=link&utm_campaign=mage_shop_link';
    }

    /**
     * Get User manual link for stores
     *
     * @return string
     */
    public function getUserManualMagentoUrl()
    {
        return 'https://www.x-payments.com/help/X-Payments:Using_X-Payments_with_Magento#Connecting_X-Payments_and_Magento';
    }

    /**
     * Get User manual link for gateways
     *
     * @return string
     */
    public function getUserManualGatewaysUrl()
    {
        return 'https://www.x-payments.com/help/X-Payments:Payment_configurations';
    }

    /**
     * Get video link
     *
     * @return string
     */
    public function getVideoUrl()
    {
        return 'https://www.youtube.com/embed/OVN8acj45ic';
    }
}
