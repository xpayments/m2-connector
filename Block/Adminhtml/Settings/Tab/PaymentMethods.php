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
 * X-Payments Connector Payment Methods settings tab
 */
class PaymentMethods extends \CDev\XPaymentsConnector\Block\Adminhtml\Settings\Tab
{
    /**
     * Current tab
     */
    protected $tab = \CDev\XPaymentsConnector\Helper\Settings::TAB_PAYMENT_METHODS;

    /**
     * Determines whether to display the tab
     * Add logic here to decide whether you want the tab to display
     *
     * @return bool
     */
    public function canShowTab()
    {
        return !$this->isWelcomeMode();
    }

    /**
     * Prepare layout
     *
     * @return void
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        if (!$this->isWelcomeMode()) {

            $this->getToolbar()->addChild(
                'add_button',
                'Magento\Backend\Block\Widget\Button',
                array(
                    'label' => __('Add new payment method'),
                    'title' => __('Add new payment method'),
                    'onclick' => sprintf('setLocation("%s")', $this->getAddNewPaymentMethodUrl()),
                    'class' => 'action-add add xpc-button',
                )
            );

            $this->getToolbar()->addChild(
                'import_button',
                'Magento\Backend\Block\Widget\Button',
                array(
                    'label' => __('Re-import payment methods'),
                    'title' => __('Re-import payment methods'),
                    'onclick' => 'submitForm("import")',
                    'class' => 'action-secondary secondary xpc-button import'
                )
            );
        }
    }

    /**
     * Get Add new payment method URL
     *
     * @return array
     */
    public function getAddNewPaymentMethodUrl()
    {
        return $this->helper->settings->getAdminUrl()
            . '?target=payment_confs';
    }
}
