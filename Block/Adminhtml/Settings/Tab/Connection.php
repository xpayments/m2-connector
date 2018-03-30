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
 * X-Payments Connector Connection settings tab
 */
class Connection extends \CDev\XPaymentsConnector\Block\Adminhtml\Settings\Tab
{
    /**
     * Current tab
     */
    protected $tab = \CDev\XPaymentsConnector\Helper\Settings::TAB_CONNECTION;

    /**
     * List of errors
     */
    private $errorList = null;

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
     * Prepare layout
     *
     * @return void
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        $this->setChild(
            'deployButton',
            $this->getLayout()->createBlock('Magento\Backend\Block\Widget\Button')
                ->setData(
                    array(
                        'type'  => 'submit',
                        'label' => __('Deploy'),
                        'class' => 'task'
                    )
                )
        );

        $this->setChild(
            'updateButton',
            $this->getLayout()->createBlock('Magento\Backend\Block\Widget\Button')
                ->setData(
                    array(
                        'type'  => 'submit',
                        'label' => __('Update'),
                        'class' => 'task'
                    )
                )
        );

        if (!empty($this->getErrorList())) {
            foreach ($this->getErrorList() as $error) {
                $this->messageManager->addError($error);
            }
        } elseif (!$this->isWelcomeMode()) {
            $this->messageManager->addSuccess(
                __('Connection with X-Payments is OK. API version: ')
                . $this->helper->settings->getXpcConfig('api_version')
            );
        }
    }

    /**
     * Get module configuration errors list
     *
     * @return array
     */
    public function getErrorList()
    {
        if (is_array($this->errorList)) {
            return $this->errorList;
        }

        if (!$this->helper->settings->checkRequirements()) {

            $this->errorList = $this->helper->settings->getRequirementsErrors();

        } elseif ($this->helper->settings->getXpcConfig('store_id')) {

            $this->errorList = $this->helper->settings->getConfigurationErrors(true);

        } else {

            // Do not display configuration errors if bundled fields are empty
            $this->errorList = array();
        }

        return $this->errorList;
    }

    /**
     * Get data for payment action selectbox
     *
     * @return array
     */
    public function getPaymentActionData()
    {
        $data = array(
            'select' => array(
                'name' => 'payment_action',
                'class' => 'select admin__control-select',
            ),
            'options' => array(
                'authorize' => array(
                    'value' => 'authorize',
                    'title' => 'Authorize',
                ),
                'authorize_capture' => array(
                    'value' => 'authorize_capture',
                    'title' => 'Authorize and capture',
                ),
            ),
        );

        $selected = ('authorize' == $this->getSettings()->getPaymentConfig('payment_action'))
            ? 'authorize'
            : 'authorize_capture';

        $data['options'][$selected]['selected'] = 'selected';

        return $data;
    }

    /**
     * Get deploy form action
     *
     * @return string
     */
    public function getDeployFormAction()
    {
        return $this->_urlBuilder->getUrl('xpc/settings/deploy');
    }

    /**
     * Get update form action
     *
     * @return string
     */
    public function getUpdateFormAction()
    {
        return $this->_urlBuilder->getUrl('xpc/settings/update');
    }
}
