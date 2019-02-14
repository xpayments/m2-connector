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

namespace CDev\XPaymentsConnector\Block\Adminhtml\Settings;

/**
 * X-Payments Connector settings container
 */
class Container extends \Magento\Backend\Block\Template
{
    /**
     * XPC Helper
     */
    protected $helper = null;

    /**
     * Constructor
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \CDev\XPaymentsConnector\Helper\Data $helper
     * @param array $data
     *
     * @return void
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \CDev\XPaymentsConnector\Helper\Data $helper,
        array $data = array()
    ) {
        $this->_template = 'settings.phtml';

        parent::__construct($context, $data);

        $this->helper = $helper;
    }

    /**
     * Check if it's welcome mode.
     * I.e. module is not configured, payment methods are not imported, etc.
     *
     * @return bool
     */
    public function isWelcomeMode()
    {
        return !$this->helper->settings->isConfigured();
    }

    /**
     * Add elements in layout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        if (!$this->isWelcomeMode()) {

            $this->getToolbar()->addChild(
                'save_button',
                'Magento\Backend\Block\Widget\Button',
                array(
                    'label' => __('Save settings'),
                    'title' => __('Save settings'),
                    'onclick' => 'submitForm("update")',
                    'class' => 'action-default primary xpc-button save'
                )
            );
        }

        return parent::_prepareLayout();
    }
}
