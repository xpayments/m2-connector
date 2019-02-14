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

namespace CDev\XPaymentsConnector\Controller\Adminhtml;

use CDev\XPaymentsConnector\Controller\RegistryConstants;

/**
 * Abstract class for X-Payments Connector configuration
 */
abstract class Settings extends \Magento\Backend\App\Action
{
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
     * Value should not exceed CDev_XPaymentsConnector_Helper_Settings_Data::MAX_SLOTS
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
     * XPC Helper
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
     * Store manager
     */
    protected $storeManager = null;

    /**
     * Core registry
     */
    protected $coreRegistry = null;

    /**
     * Check if action is allowed
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('CDev_XPaymentsConnector::settings');
    }

    /**
     * Constructor
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $pageFactory
     * @param \Magento\Framework\Controller\ResultFactory $resultFactory
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \CDev\XPaymentsConnector\Model\PaymentConfigurationFactory $pcFactory
     * @param \CDev\XPaymentsConnector\Helper\Data $helper
     *
     * @return void
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $pageFactory,
        \Magento\Framework\Controller\ResultFactory $resultFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Registry $coreRegistry,
        \CDev\XPaymentsConnector\Model\PaymentConfigurationFactory $pcFactory,
        \CDev\XPaymentsConnector\Helper\Data $helper
    ) {
        
        $this->pageFactory = $pageFactory;
        $this->resultFactory = $resultFactory;
        $this->messageManager = $messageManager;
        $this->storeManager = $storeManager;
        $this->coreRegistry = $coreRegistry;
        $this->pcFactory = $pcFactory;

        $this->helper = $helper;

        parent::__construct($context);
    }

    /**
     * Initialize current Store ID
     *
     * @return int
     */
    protected function initCurrentStoreId()
    {
        $storeId = (int)$this->getRequest()->getParam('store', 0);

        $this->coreRegistry->register(RegistryConstants::CURRENT_STORE_ID, $storeId);

        return $storeId;
    }

    /**
     * Clear ipmorted payment configurations
     *
     * @return void
     */
    protected function clearImportedConfigurations()
    {
        $list = $this->pcFactory->create()->getCurrentStoreCollection();

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
            }
        }

        // Delete all payment configurations for this store
        $list->walk('delete');

        // Unset currency
        $this->helper->settings->setXpcConfig('currency', '');

        // Deactivate payment methods
        $this->helper->settings->setPaymentConfig('active', false);
        $this->helper->settings->setPaymentCardConfig('active', false);
        $this->helper->settings->setXpcConfig('active_confid', 0);

        // Unset save cards and zero-auth flags
        $this->helper->settings->setXpcConfig('can_save_cards', false);
        $this->helper->settings->setXpcConfig('zero_auth_active', false);
    }

    /**
     * Update payment configurations
     *
     * @return void
     */
    protected function updatePaymentConfigurations()
    {
        $list = $this->pcFactory->create()->getCurrentStoreCollection();

        $activeConfId = $this->getRequest()->getParam('active_confid');
        $saveCards = $this->getRequest()->getParam('save_cards');
        $canSaveCards = false;

        $currency = '';

        foreach ($list as $paymentConf) {

            $confId = $paymentConf->getConfid();

            $isActive = ($activeConfId == $confId);
            $isSaveCards = (!empty($saveCards[$confId]) && 'Y' === $saveCards[$confId]);

            if ($isActive) {

                // Save currency for active payment configuration
                $currency = $paymentConf->getCurrency();

                if ($isSaveCards) {
                    // Activate saved payment cards payment method
                    // if save cards is enabled for active payment configuration
                    $canSaveCards = true;
                }
            }

            $paymentConf->setIsActive($isActive)
                ->setSaveCards($isSaveCards);
        }

        $list->walk('save');

        $this->helper->settings->setXpcConfig('active_confid', $activeConfId);
        $this->helper->settings->setXpcConfig('can_save_cards', $canSaveCards);
        $this->helper->settings->setPaymentCardConfig('active', $canSaveCards);

        $this->helper->settings->setXpcConfig('currency', $currency);
    }

    /**
     * Update payment method
     *
     * @return void
     */
    protected function updatePaymentMethod()
    {
        $paymentMethod = $this->getRequest()->getParam('payment_method');

        if (!empty($paymentMethod) && is_array($paymentMethod)) {

            if (
                isset($paymentMethod['specificcountry'])
                && is_array($paymentMethod['specificcountry'])
                && $paymentMethod['allowspecific']
            ) {
                $paymentMethod['specificcountry'] = implode(',', $paymentMethod['specificcountry']);
            } else {
                $paymentMethod['specificcountry'] = '';
            }

            foreach ($paymentMethod as $key => $value) {

                $this->helper->settings->setPaymentConfig($key, $value);
            }
        }
    }

    /**
     * Subaction (mode) to update the payment methods
     *
     * @return void
     */
    protected function updatePaymentMethods()
    {
        $this->updatePaymentConfigurations();

        $this->updatePaymentMethod();

        $this->helper->settings->flushCache();
    }

    /**
     * Import payment methods
     *
     * @return void
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function importPaymentMethods()
    {
        // Clear existing payment configurations, but save their data.
        // Disable all X-Payments payment methods including the "Saved cards" one.
        $this->clearImportedConfigurations();

        // Obtain payment methods from X-Payments
        $list = $this->helper->api->requestPaymentMethods();

        if (empty($list)) {

            // Some strange magic with translations here. Do not use constants
            // MEQP2.Translation.ConstantUsage.VariableTranslation
            $message = 'Payment methods import failed. '
                . 'Make sure you’ve activated your payment configurations '
                . 'and assigned them to this store in X-Payments dashboard.';

            throw new \Magento\Framework\Exception\LocalizedException(__($message));
        }

        foreach ($list as $data) {

            $paymentConfigurationData = $this->fillPaymentConfigurationData($data);

            // Save payment configuration data
            $pc = $this->pcFactory->create();
            $pc->setData($paymentConfigurationData);
            $pc->save();

            if ($paymentConfigurationData['is_active']) {

                // Re-active payment method if it was active before re-import
                $this->helper->settings->setPaymentConfig('active', true);

                $this->helper->settings->setXpcConfig('currency', $paymentConfigurationData['currency']);
            }
        }

        $this->helper->settings->flushCache();
    }

    /**
     * Activate zero-auth if active payment configuration can save cards
     *
     * @return void
     */
    protected function autoActivateZeroAuth()
    {
        if (
            0 !== (int)$this->helper->settings->getXpcConfig('active_confid')
            && $this->helper->settings->getXpcConfig('can_save_cards')
        ) {

            $this->helper->settings->setXpcConfig('zero_auth_active', true);
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
            'can_save_cards' => 'Y' === $data['canSaveCards'],
            'save_cards'     => 'Y' === $data['canSaveCards'], // Auto-enable recharges if possible
            'currency'       => $data['currency'],
            'is_active'      => false,
            'store_id'       => (int)$this->coreRegistry->registry(RegistryConstants::CURRENT_STORE_ID),
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

        if ($paymentConf instanceof \CDev\XPaymentsConnector\Model\PaymentConfiguration) {

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
    protected function redirectToTab($tab = \CDev\XPaymentsConnector\Helper\Settings::TAB_CONNECTION)
    {
        $resultRedirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);

        $params = array(
            'active_tab' => $tab,
            'store'      => (int)$this->coreRegistry->registry(RegistryConstants::CURRENT_STORE_ID),
        );

        $resultRedirect->setPath('xpc/settings/index', $params);

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
