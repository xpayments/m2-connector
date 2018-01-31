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

namespace Cdev\XPaymentsConnector\Block\Adminhtml\Settings\Tab;

/**
 * X-Payments Connector Payment Methods settings tab
 */
class PaymentMethods extends \Cdev\XPaymentsConnector\Block\Adminhtml\Settings\Tab
{
    /**
     * Current tab
     */
    protected $tab = \Cdev\XPaymentsConnector\Helper\Settings::TAB_PAYMENT_METHODS;

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

        $this->setChild(
            'updateButton',
            $this->getLayout()->createBlock('Magento\Backend\Block\Widget\Button')
                ->setData(
                    array(
                        'type'  => 'submit',
                        'label' => __('Update'),
                        'class' => 'task',
                        'onclick' => 'javascript: setFormMode("update");'
                    )
                )
        );

        $this->setChild(
            'importButton',
            $this->getLayout()->createBlock('Magento\Backend\Block\Widget\Button')
                ->setData(
                    array(
                        'type'    => 'submit',
                        'label'   => __('Re-import payment methods'),
                        'class'   => 'task',
                        'onclick' => 'javascript: setFormMode("import");'
                    )
                )
        );
    }

    /**
     * Get data for payment method title input
     *
     * @param $pm Payment method data
     *
     * @return array
     */
    private function getTitleInput($pm)
    {
        return array(
            'name' => 'payment_method_data[' . $pm['confid'] . '][title]',
            'value' => $pm['payment_method_data']['title'],
        );
    }

    /**
     * Get data for payment method sort order input
     *
     * @param $pm Payment method data
     *
     * @return array
     */
    private function getSortOrderInput($pm)
    {
        return array(
            'name' => 'payment_method_data[' . $pm['confid'] . '][sort_order]',
            'value' => $pm['payment_method_data']['sort_order'],
        );
    }

    /**
     * Get data for list of specific countries selectbox
     *
     * @param array $pm Payment method data
     *
     * @return array
     */
    private function getSpecificCountriesSelectBox($pm)
    {
        // TODO: implement
        $countries = array();//Mage::getResourceModel('directory/country_collection')->loadData()->toOptionArray(false);

        $result = array(
            'select' => array(
                'name' => 'payment_method_data[' . $pm['confid'] . '][specificcountry][]',
                'multiple' => 'multiple',
                'class' => 'select multiselect specificcountry',
                'size' => 10,
            ),
            'options' => array(),
        );

        $selected = explode(',', $pm['payment_method_data']['specificcountry']);

        foreach ($countries as $country) {

            $code = $country['value'];

            $result['options'][$code] = array(
                'title' => $country['label'],
                'value' => $code,
            );

            if (in_array($code, $selected)) {
                $result['options'][$code]['selected'] = 'selected';
            }
        }

        return $result;
    }

    /**
     * Get data for Yes/No selectbox
     *
     * @param array $pm Payment method data
     * @param string $name Element name
     * @param bool $isYes Should be Yes selected by default
     *
     * @return array
     */
    private function getYesNoSelectBox($pm, $name, $isYes = false)
    {
        $result = array(
            'select' => array(
                'name' => 'payment_method_data[' . $pm['confid'] . '][' . $name . ']',
                'class' => $name,
            ),
            'options' => array(
                1 => array(
                     'title' => 'Yes',
                     'value' => '1',
                ),
                0 => array(
                    'title' => 'No',
                    'value' => '0',
                ),
            ),
        );

        if (isset($pm['payment_method_data'][$name])) {
            $key = $pm['payment_method_data'][$name] ? 1 : 0;
        } else {
            $key = $isYes ? 1 : 0;
        }

        $result['options'][$key]['selected'] = 'selected';

        return $result;
    }

    /**
     * Get list of imported payment configurations
     *
     * @param bool $includeDisabled Include "disable all" fake payment method or not
     *
     * @return array
     */
    public function getPaymentMethods($includeDisabled = true)
    {
        $list = $this->pcFactory->create()->getCollection();

        $activeCount = 0;

        $result = array();

        if (!empty($list)) {

            $count = 0;

            foreach ($list as $k => $v) {

                $data = $v->getData();

                $confId = $data['confid'];

                $data['payment_method_data'] = json_decode($data['payment_method_data'], true);

                $result[$confId] = $data;

                // Enable/disable checkbox
                $result[$confId]['active_checkbox'] = array(
                    'id'    => 'active-checkbox-' . $confId,
                    'name'  => 'payment_methods[active][' . $confId . ']',
                    'value' => 'Y',
                    'class' => 'pm-active pointer ' . ($count++ % 2 == 0 ? 'even' : 'odd'),
                );

                // Active radio
                $result[$confId]['active_radio'] = array(
                    'id'    => 'active-radio-' . $confId,
                    'name'  => 'active_confid',
                    'value' => $confId,
                    'class' => 'pm-active pointer ' . ($count++ % 2 == 0 ? 'even' : 'odd'),
                );

                if (
                    $data['is_active']
                    && $activeCount < \Cdev\XPaymentsConnector\Helper\Settings::MAX_SLOTS
                ) {
                    $result[$confId]['active_checkbox'] += array('checked' => 'checked');
                    $result[$confId]['active_radio'] += array('checked' => 'checked');
                    $activeCount++;
                }

                // Save cards checkbox
                $result[$confId]['savecard_checkbox'] = array(
                    'id'    => 'savecard-checkbox-' . $data['confid'],
                    'name'  => 'payment_methods[savecard][' . $data['confid'] . ']',
                    'value' => 'Y',
                    'class' => 'pm-savecard',
                );

                if ('Y' == $data['save_cards']) {
                    $result[$confId]['savecard_checkbox'] += array('checked' => 'checked');
                }

                // Colspan for payment methods table
                if ($this->isCanSaveCards()) {
                    $result[$confId]['colspan'] = !empty($data['can_save_cards']) ? '3' : '2';
                } else {
                    $result[$confId]['colspan'] = '1';
                }

                // Payment method block
                $result[$confId]['payment_method'] = array(
                    'id'                        => 'payment-method-' . $confId,
                    'title'                     => $this->getTitleInput($data),
                    'sort_order'                => $this->getSortOrderInput($data),
                    'allowspecific'             => $this->getYesNoSelectBox($data, 'allowspecific'),
                    'specificcountry'           => $this->getSpecificCountriesSelectBox($data),
                    'use_authorize'             => $this->getYesNoSelectBox($data, 'use_authorize'),
                    'use_initialfee_authorize'  => $this->getYesNoSelectBox($data, 'use_initialfee_authorize'),
                );

            }
        }

        if ($includeDisabled) {

            // Add the "disabled" row
            $result[0] = array(
                'confid'  => 0,
                'name'    => __('Disable X-Payments payment method'),
                'active_checkbox' => array(
                    'id'    => 'active-checkbox-0',
                    'name'  => 'payment_methods[active][0]',
                    'value' => 'Y',
                    'class' => 'pm-disable pointer ' . ($count++ % 2 == 0 ? 'even' : 'odd'),
                ),
                'active_radio' => array(
                    'id'    => 'active-radio-0',
                    'name'  => 'active_confid',
                    'value' => '0',
                    'class' => 'pm-disable pointer ' . ($count++ % 2 == 0 ? 'even' : 'odd'),
                ),
                'colspan'   => '3',
            );

            if (0 == $activeCount) {
                $result[0]['active_checkbox']['checked'] = 'checked';
                $result[0]['active_radio']['checked'] = 'checked';
            }
        }

        return $result;
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
