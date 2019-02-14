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

namespace CDev\XPaymentsConnector\Block\Adminhtml\Settings\Field;

/**
 * Select-box configuration field
 */
class Select extends \Magento\Backend\Block\Template
{
    /**
     * XPC Helper
     */
    protected $helper = null;

    /**
     * Allowed countries model
     */
    protected $allowedCountries = null;

    /**
     * Country info intreface
     */
    protected $countryInfo = null;

    /**
     * Constructor
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \CDev\XPaymentsConnector\Helper\Data $helper
     * @param \Magento\Directory\Model\AllowedCountries $allowedCountries
     * @param \Magento\Directory\Api\CountryInformationAcquirerInterface $countryInfo
     * @param array $data
     *
     * @return void
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \CDev\XPaymentsConnector\Helper\Data $helper,
        \Magento\Directory\Model\AllowedCountries $allowedCountries,
        \Magento\Directory\Api\CountryInformationAcquirerInterface $countryInfo,
        array $data = array()
    ) {
        $this->_template = 'settings/field/select.phtml';

        parent::__construct($context, $data);

        $this->helper = $helper;
        $this->allowedCountries = $allowedCountries;
        $this->countryInfo = $countryInfo;
    }

    /**
     * Check if option is selected
     *
     * @param string $value OPtion value
     *
     * @return bool
     */
    public function isSelected($value)
    {
        if (!$this->getIsMultiple()) {
            $result = ($this->getFieldValue() == $value);
        } else {
            $result = in_array($value, $this->getSelectedValues());
        }

        return $result;
    }

    /**
     * Set params for config field
     *
     * @param string $title
     * @param string $name
     * @param string $key Key from config (last from core_config_data)
     *
     * @return void
     */
    public function setFieldParams($title = null, $name = null, $key = null)
    {
        if ($title) {
            $this->setFieldTitle($title);
        }

        if ($name) {
            $this->setFieldName(sprintf('payment_method[%s]', $name));
        }

        if ($key) {
            $this->setFieldValue($this->helper->settings->getPaymentConfig($key));
        }
    }

    /**
     * Set options for Yes/No select-box
     *
     * @return void
     */
    public function setYesNoOptions()
    {
        $options = array(
            '1' => 'Yes',
            '0' => 'No',
        );

        $this->setFieldOptions($options);
        $this->setIsMultiple(false);
        $this->setIsDisabled(false);
    }

    /**
     * Set options for payment initial action select-box
     *
     * @return void
     */
    public function setPaymentActionOptions()
    {
        $options = array(
            'authorize'         => 'Authorize Only',
            'authorize_capture' => 'Authorize and Capture',
        );

        $this->setFieldOptions($options);
        $this->setIsMultiple(false);
        $this->setIsDisabled(false);
    }

    /**
     * Set options for allow specific country select-box
     *
     * @return void
     */
    public function setAllowSpecificOptions()
    {
        $options = array(
            '0' => 'All Allowed Countries',
            '1' => 'Specific Countries',
        );

        $this->setFieldOptions($options);
        $this->setIsMultiple(false);
        $this->setIsDisabled(false);
        $this->setOnChange('switchCountries();');
    }

    /**
     * Set options for specific countries list select-box
     *
     * @return void
     */
    public function setSpecificCountryOptions()
    {
        $specificCountries = $this->helper->settings->getPaymentConfig('specificcountry');

        if (!empty($specificCountries)) {
            $selected = explode(',', $specificCountries);
        } else {
            $selected = array();
        }

        $countries = $this->allowedCountries->getAllowedCountries();

        $disabled = ('0' === $this->helper->settings->getPaymentConfig('allowspecific'));

        $this->setFieldName('payment_method[specificcountry][]');
        $this->setFieldOptions($countries);
        $this->setSelectedValues($selected);
        $this->setIsMultiple(true);
        $this->setIsDisabled($disabled);
    }

    /**
     * Set options for zero-auth enabled select-box
     *
     * @return void
     */
    public function setZeroAuthEnabledParams()
    {
        $this->setFieldValue($this->helper->settings->getXpcConfig('zero_auth_active'));
        $this->setFieldName('zero_auth_active');
    }
}
