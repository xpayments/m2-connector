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
 * @category   Cdev
 * @package    Cdev_XPaymentsConnector
 * @copyright  (c) 2010-present Qualiteam software Ltd <info@x-cart.com>. All rights reserved
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Cdev\XPaymentsConnector\Block\Adminhtml\Settings;

/**
 * Abstract class for tab on the settings page
 */
abstract class Tab extends \Magento\Backend\Block\Template implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * Block template
     */
    protected $_template = '';

    /**
     * Current tab
     */
    protected $tab = null;

    /**
     * Helper
     */
    protected $helper = null;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

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
     * @return \Cdev\XPaymentsConnector\Helper\Settings
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
        return $this->getViewFileUrl('Cdev_XPaymentsConnector::images/' . $image);
    }

    /**
     * Check if at least one of the payment configurations can be used for saving cards
     *
     * @return bool
     */
    public function isCanSaveCards()
    {
        // TODO: implement
        return false;
    }

    /**
     * Constructor
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param array $data
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Cdev\XPaymentsConnector\Model\PaymentConfigurationFactory $pcFactory
     * @param \Cdev\XPaymentsConnector\Helper\Data $helper
     *
     * @return void
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        array $data = array(),
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Cdev\XPaymentsConnector\Model\PaymentConfigurationFactory $pcFactory,
        \Cdev\XPaymentsConnector\Helper\Data $helper
    ) {

        $this->_template = 'settings/tab/' . $this->tab . '.phtml';
        $this->_coreRegistry = $registry;

        parent::__construct($context, $data);

        $this->pcFactory = $pcFactory;

        $this->messageManager = $messageManager;

        $this->helper = $helper;
    }

    /**
     * Get attributes string
     * param1="value1" param2="value2"...
     *
     * @param array $data Attributes list
     *
     * @return string
     */
    private function getAttributesStr($data)
    {
        $str = '';

        foreach ($data as $k => $v) {
            $str .= $k . '=' . '"' . $this->escapeHtmlAttr($v) . '" ';
        }

        return $str;
    }

    /**
     * Get HTML code for selectbox
     *
     * @param array $data Selectbox data
     *
     * @return string
     */
    public function getSelectboxHtml($data)
    {
        $str = '<select '
            . $this->getAttributesStr($data['select'])
            . '>' . PHP_EOL;

        foreach ($data['options'] as $option) {
            $title = $option['title'];
            unset($option['title']);
            $str .= '<option ' . $this->getAttributesStr($option) . '>'
                . __($title)
                . '</option>' . PHP_EOL;
        }

        $str .= '</select>';

        return $str;
    }

    /**
     * Get HTML code for checkbox
     *
     * @param array $data Checkbox data
     *
     * @return string
     */
    public function getCheckboxHtml($data)
    {
        $str = '<input type="checkbox" '
            . $this->getAttributesStr($data)
            . '/>';

        return $str;
    }

    /**
     * Get HTML code for radio
     *
     * @param array $data Checkbox data
     *
     * @return string
     */
    public function getRadioHtml($data)
    {
        $str = '<input type="radio" '
            . $this->getAttributesStr($data)
            . '/>';

        return $str;
    }

    /**
     * Get HTML code for input
     *
     * @param array $data Input data
     *
     * @return string
     */
    public function getInputHtml($data)
    {
        $str = '<input type="text" class="input-text" '
            . $this->getAttributesStr($data)
            . '/>';

        return $str;
    }
}
