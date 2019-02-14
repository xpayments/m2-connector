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

use CDev\XPaymentsConnector\Controller\RegistryConstants;

/**
 * Abstract class for tab on the settings page
 */
abstract class Tab extends \Magento\Backend\Block\Template implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * Current tab
     */
    protected $tab = null;

    /**
     * XPC Helper
     */
    protected $helper = null;

    /**
     * Core coreRegistry
     */
    protected $coreRegistry = null;

    /**
     * Message manager interface
     */
    protected $messageManager = null;

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
     * Retrieve the label used for the tab relating to this block
     *
     * @return string
     */
    public function getTabLabel()
    {
        $tabs = $this->helper->settings->getTabs();

        return $tabs[$this->tab];
    }

    /**
     * Retrieve the title used by this tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        $tabs = $this->helper->settings->getTabs();

        return $tabs[$this->tab];
    }

    /**
     * Determines whether to display the tab
     * Add logic here to decide whether you want the tab to display
     *
     * @return bool
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Stops the tab being hidden
     *
     * @return bool
     */
    public function isHidden()
    {
        return !$this->canShowTab();
    }

    /**
     * Settings helper
     *
     * @return \CDev\XPaymentsConnector\Helper\Settings
     */
    public function getSettings()
    {
        return $this->helper->settings;
    }

    /**
     * Get image URL
     *
     * @return string
     */
    public function getImageUrl($image)
    {
        return $this->getViewFileUrl('CDev_XPaymentsConnector::images/' . $image);
    }

    /**
     * Check if at least one of the payment configurations can be used for saving cards
     *
     * @return bool
     */
    public function isCanSaveCards()
    {
        return $this->helper->settings->isConfigured()
            && $this->helper->settings->getXpcConfig('can_save_cards');
    }

    /**
     * Constructor
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \CDev\XPaymentsConnector\Helper\Data $helper
     * @param array $data
     *
     * @return void
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \CDev\XPaymentsConnector\Helper\Data $helper,
        array $data = array()
    ) {

        $this->coreRegistry = $coreRegistry;

        parent::__construct($context, $data);

        $this->messageManager = $messageManager;

        $this->helper = $helper;
    }

    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_template = 'settings/tab/' . $this->tab . '.phtml';
    }

    /**
     * Get current Store ID
     *
     * @return int
     */
    public function getCurrentStoreId()
    {
        return (int)$this->coreRegistry->registry(RegistryConstants::CURRENT_STORE_ID);
    }
}
