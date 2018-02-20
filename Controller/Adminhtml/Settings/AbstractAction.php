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
 * Abstract class for X-Payments Connector configuration
 */
abstract class AbstractAction extends \Magento\Backend\App\Action
{
    /**
     * Some error messages
     */
    const ERROR_EMPTY_IMPORTED_METHODS = 'Payment methods import failed. Make sure you’ve activated your payment configurations and assigned them to this store in X-Payments dashboard.';
    const ERROR_INVALID_BUNDLE = 'Invalid configuration bundle';

    /**
     * This is the remembered data about payment configurations during re-import
     */
    private $remember = array();

    /**
     * Data POST-ed to the controller for update payment metods action
     */
    private $updateData = array();

    /**
     * Payment method data POST-ed to the controller for update payment metods action
     */
    private $methodData = array();

    /**
     * Here we count the active payment configurations during the update payment metods action.
     * Value should not exceed XPay_XPaymentsConnector_Helper_Settings_Data::MAX_SLOTS
     */
    private $activeCount = 0;

    /**
     * If at least one active payment configurations is configured to save cards
     */
    private $saveCardsActive = false;

    /**
     * Default data for payment configuration (e.g. just imported)
     */
    private $defaultPaymentData = array(
        'title' => 'Credit card (X-Payments)',
        'sort_order' => '0',
        'allowspecific' => '0',
        'specificcountry' => '',
        'use_authorize' => '0',
        'use_initialfee_authorize' => '0',
    );

    /**
     * View page factory (to display the page)
     */
    protected $pageFactory = null;

    /**
     * Result factory (for redirects)
     */
    protected $resultFactory = null;

    /**
     * Helper
     */
    protected $helper = null;

    /**
     * Payment configuration factory
     */
    protected $pcFactory = null;

    /**
     * Message manager interface
     */
    protected $messageManager = null;

    /**
     * Constructor
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $pageFactory
     * @param \Magento\Framework\Controller\ResultFactory $resultFactory
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \XPay\XPaymentsConnector\Model\PaymentConfigurationFactory $pcFactory
     * @param \XPay\XPaymentsConnector\Helper\Data $helper
     *
     * @return void
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $pageFactory,
        \Magento\Framework\Controller\ResultFactory $resultFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \XPay\XPaymentsConnector\Model\PaymentConfigurationFactory $pcFactory,
        \XPay\XPaymentsConnector\Helper\Data $helper
    ) {
        
        $this->pageFactory = $pageFactory;
        $this->resultFactory = $resultFactory;
        $this->messageManager = $messageManager;
        $this->pcFactory = $pcFactory;

        $this->helper = $helper;

        parent::__construct($context);
    }

    /**
     * Deactivate unused or "extra" payment methods, including the "Saved cards" one
     *
     * @param int $startId Slot index of the XPC payment method to start with
     *
     * @return void
     */
    private function deactivatePaymentMethods($startSlot = 1)
    {
        // Deactivate
        for ($xpcSlot = $startSlot; $xpcSlot <= \XPay\XPaymentsConnector\Helper\Settings::MAX_SLOTS; $xpcSlot++) {
            $this->helper->settings->setPaymentConfig('is_active', 0, $xpcSlot);
        }

        // Deactivate "Saved cards" payment method
        $this->helper->settings->setSavedCardsConfig('is_active', false);
    }

    /**
     * Clear ipmorted payment methods
     *
     * @return void
     */
    private function clearPaymentMethods()
    {
        $list = $this->pcFactory->create()->getCollection();

        if ($list) {
            foreach ($list as $pc) {

                $this->remember[$pc->getConfid()] = array(
                    'name'       => $pc->getName(),
                    'module'     => $pc->getModule(),
                    'hash'       => $pc->getHash(),
                    'save_cards' => $pc->getSaveCards(),
                    'is_active'  => $pc->getIsActive(),
                    'data'       => $pc->getPaymentMethodData(),
                );

                $pc->delete();
            }
        }

        // Deactivate all payment methods
        $this->deactivatePaymentMethods();
    }

    /**
     * Process data for payment method associated with the payment configuration
     *
     * @param XPay_XPaymentsConnector_Model_Paymentconfiguration $paymentConf Payment configuration
     *
     * @return void
     */
    private function processPaymentConfMethod(\XPay\XPaymentsConnector\Model\Paymentconfiguration $paymentConf)
    {
        if ('Y' == $paymentConf->getActive()) {

            $confId = $paymentConf->getConfid();
            $data = json_decode($paymentConf->getPaymentMethodData(), true);

            $data += array(
                'confid' => $confId,
                'is_active' => true,
            );

            foreach ($data as $name => $value) {
                $this->helper->settings->setPaymentConfig($name, $value, $this->activeCount);
            }
        }
    }

    /**
     * Set value for the payment method data param
     *
     * @param XPay_XPaymentsConnector_Model_Paymentconfiguration $paymentConf Payment configuration
     *
     * @return XPay_XPaymentsConnector_Model_Paymentconfiguration
     */
    protected function setPaymentMethodDataParamValue(\XPay\XPaymentsConnector\Model\Paymentconfiguration $paymentConf)
    {
        $confId = $paymentConf->getConfid();

        if (isset($this->methodData[$confId])) {

            $pmd = $this->methodData[$confId];

            if (
                isset($pmd['specificcountry'])
                && is_array($pmd['specificcountry'])
                && $pmd['allowspecific']
            ) {
                $pmd['specificcountry'] = implode(',', $pmd['specificcountry']);
            } else {
                $pmd['specificcountry'] = '';
            }

        } else {

            $pmd = $this->getDefaultPaymentData($paymentConf);
        }

        return $paymentConf->setPaymentMethodData(json_encode($pmd));
    }

    /**
     * Set value for the Active param
     *
     * @param XPay_XPaymentsConnector_Model_Paymentconfiguration $paymentConf Payment configuration
     *
     * @return XPay_XPaymentsConnector_Model_Paymentconfiguration
     */
    protected function setActiveParamValue(\XPay\XPaymentsConnector\Model\Paymentconfiguration $paymentConf)
    {
        $confId = $paymentConf->getConfid();

        if (
            !isset($this->updateData['is_active'][$confId])
            || true != $this->updateData['is_active'][$confId]
            || $this->activeCount > \XPay\XPaymentsConnector\Helper\Settings::MAX_SLOTS
        ) {
            $active = false;
        } else {
            $active = true;
            $this->activeCount++;
        }

        $active = ($confId == $this->getRequest()->getParam('active_confid'));

        if ($active) {
            $this->activeCount = 1;
            $this->helper->settings->setXpcConfig('active_confid', $confId);
        }

        return $paymentConf->setIsActive($active);
    }

    /**
     * Subaction (mode) to update the payment methods
     *
     * @return void
     */
    protected function updatePaymentMethods()
    {
        $list = $this->pcFactory->create()->getCollection();

        $this->saveCardsActive = false;
        $this->activeCount = 0;

        $this->updateData = $this->getRequest()->getParam('payment_methods');
        $this->methodData = $this->getRequest()->getParam('payment_method_data');

        $this->helper->settings->markMethodActive(0 != $this->getRequest()->getParam('active_confid'));

        foreach ($list as $paymentConf) {

            // Save data for payment configuration
            $this->setActiveParamValue($paymentConf);
            // TODO: Implement later
            // $this->setSaveCardsParamValue($paymentConf);
            $this->setPaymentMethodDataParamValue($paymentConf);

            $paymentConf->save();

            // Update payment method
            $this->processPaymentConfMethod($paymentConf);
        }

        // Deactivate "extra" payment methods
        $this->deactivatePaymentMethods($this->activeCount + 1);

        // Update saved cards payment method
        // TODO: Implement later
        // $this->processSavedCardsMethod();
    }

    /**
     * Import payment methods
     *
     * @return void
     */
    protected function importPaymentMethods()
    {
        // Clear existing payment configurations, but save their data.
        // Disable all X-Payments payment methods including the "Saved cards" one.
        $this->clearPaymentMethods();

        // Obtain payment methods from X-Payments
        $list = $this->helper->api->requestPaymentMethods();

        if (empty($list)) {
            throw new \Exception(self::ERROR_EMPTY_IMPORTED_METHODS);
        }

        foreach ($list as $data) {

            $paymentConfigurationData = $this->fillPaymentConfigurationData($data);

            // Save payment configuration data
            $pc = $this->pcFactory->create();
            $pc->setData($paymentConfigurationData);
            $pc->save();

            // Re-active payment method if it was active before re-import
            if ($paymentConfigurationData['is_active']) {

                try {

                    $xpcSlot = $this->helper->settings->getXpcSlotByConfid($data['id']);
                    $this->helper->settings->setPaymentConfig('is_active', true, $xpcSlot);

                } catch (\Exception $e) {

                    // Just in case.
                    // This payment configuration is not associated with payment method.
                }
            }
        }
    }

    /**
     * Fill payment configuration data
     *
     * @param array $data Payment configuration data passed from X-Payments
     *
     * @return array
     */
    private function fillPaymentConfigurationData($data)
    {
        $confId = $data['id'];

        $pcData = array(
            'confid'         => $confId,
            'name'           => $data['name'],
            'module'         => $data['moduleName'],
            'hash'           => $data['settingsHash'],
            'can_save_cards' => $data['canSaveCards'],
            'save_cards'     => $data['canSaveCards'], // Auto-enable recharges if possible
            'currency'       => $data['currency'],
            'is_active'      => false,
            'payment_method_data' => json_encode($this->getDefaultPaymentData($data)),
        );

        if (isset($this->remember[$confId])) {

            $remember = $this->remember[$confId];

            if (
                $remember['name'] == $pcData['name']
                || $remember['module'] == $pcData['module']
                || $remember['hash'] == $pcData['hash']
            ) {

                // Restore save cards flag
                $pcData['save_cards'] = $remember['save_cards'];

                // Resore active flag
                $pcData['is_active'] = $remember['is_active'];

                // Resore payment method data
                $pcData['payment_method_data'] = $remember['data'];

                // Remembered data matches the imported one
                $this->remember[$confId]['match'] = true;

            } else {

                // Remembered data doesn't match the imported one
                $this->remember[$confId]['match'] = false;
            }

        } else {

            // This is a new/unknown payment configuration
            $this->remember[$confId] = array(
                'match' => false,
            );
        }

        return $pcData;
    }

    /**
     * Get default payment method data
     *
     * @param mixed $paymentConf Payment configuration
     *
     * @return array
     */
    private function getDefaultPaymentData($paymentConf = null)
    {
        $result = $this->defaultPaymentData;

        if ($paymentConf instanceof \XPay\XPaymentsConnector\Model\PaymentConfiguration) {

            $result['title'] = 'Credit card (' . $paymentConf->getData('name') . ')';

        } elseif (
            is_array($paymentConf)
            && !empty($paymentConf['name'])
        ) {

            $result['title'] = 'Credit card (' . $paymentConf['name'] . ')';
        }

        return $result;
    }

    /**
     * Redirect to the specific tab at settings page
     *
     * @param string $tab Tab
     *
     * @return void
     */
    protected function redirectToTab($tab = \XPay\XPaymentsConnector\Helper\Settings::TAB_CONNECTION)
    {
        $resultRedirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath('xpc/settings/index', array('active_tab' => $tab));

        return $resultRedirect;
    }

    /**
     * Add success top message
     *
     * @param string $message Message
     *
     * @return void
     */
    protected function addSuccessTopMessage($message)
    {
        $this->messageManager->addSuccessMessage(__($message));
    }

    /**
     * Add error top message to session
     *
     * @param string $message Message
     *
     * @return void
     */
    protected function addErrorTopMessage($message)
    {
        $this->messageManager->addErrorMessage(__($message));
    }
}
