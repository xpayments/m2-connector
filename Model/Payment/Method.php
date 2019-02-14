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

namespace CDev\XPaymentsConnector\Model\Payment;

use CDev\XPaymentsConnector\Controller\RegistryConstants;
use CDev\XPaymentsConnector\Helper\Api;

/**
 * Abstract payment method
 */
abstract class Method extends \Magento\Payment\Model\Method\AbstractMethod
{
    /**
     * Names for some actions missing in core
     */
    const ACTION_SALE = 'sale';
    const ACTION_CAPTURE = 'capture';
    const ACTION_VOID = 'void';
    const ACTION_REFUND = 'refund';

    /**
     * Payment Info block
     */
    protected $_infoBlockType = \CDev\XPaymentsConnector\Block\Payment\Info::class;

    /**
     * Payment action
     */
    protected $paymentAction = '';

    /**
     * Customer session
     */
    protected $customerSession = null;
    protected $checkoutSession = null;

    /**
     * XPC Quote Data factory
     */
    protected $quoteDataFactory = null;

    /**
     * Payment Card Factory
     */
    protected $paymentCardFactory = null;

    /**
     * XPC Helper
     */
    protected $helper = null;

    /**
     * Core coreRegistry
     */
    protected $coreRegistry = null;

    /**
     * Store manager
     */
    protected $storeManager = null;

    /**
     * URL Builder
     */
    protected $urlBuilder = null;
    
    /**
     * Application state
     */
    protected $appState = null;

    /**
     * Session Quote
     */
    protected $sessionQuote = null;

    /**
     * Constructor
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory
     * @param \Magento\Payment\Helper\Data $paymentData
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Payment\Model\Method\Logger $logger
     * @param \CDev\XPaymentsConnector\Model\QuoteDataFactory $quoteDataFactory
     * @param \CDev\XPaymentsConnector\Model\PaymentCardFactory $paymentCardFactory
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Customer\Model\Session\Proxy $customerSession
     * @param \Magento\Checkout\Model\Session\Proxy $checkoutSession
     * @param \Magento\Backend\Model\Session\Quote $sessionQuote
     * @param \Magento\Framework\App\State $appState
     * @param \CDev\XPaymentsConnector\Helper\Data $helper
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     *
     * @return void
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Method\Logger $logger,
        \CDev\XPaymentsConnector\Model\QuoteDataFactory $quoteDataFactory,
        \CDev\XPaymentsConnector\Model\PaymentCardFactory $paymentCardFactory,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\Session\Proxy $customerSession,
        \Magento\Checkout\Model\Session\Proxy $checkoutSession,
        \Magento\Backend\Model\Session\Quote $sessionQuote,
        \Magento\Framework\App\State $appState,
        \CDev\XPaymentsConnector\Helper\Data $helper,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = array()
    ) {

        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            $resource,
            $resourceCollection,
            $data
        );

        $this->quoteDataFactory = $quoteDataFactory;
        $this->paymentCardFactory = $paymentCardFactory;
        $this->customerSession = $customerSession;
        $this->checkoutSession = $checkoutSession;
        $this->sessionQuote = $sessionQuote;
        $this->appState = $appState;
        $this->helper = $helper;
        $this->urlBuilder = $urlBuilder;
        $this->coreRegistry = $coreRegistry;
        $this->storeManager = $storeManager;
    }

    /**
     * Check if it's admin area
     *
     * @return bool
     */
    protected function isAdmin()
    {
        return 'adminhtml' == $this->appState->getAreaCode();
    }

    /**
     * Check if payment method is available
     *
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     *
     * @return bool
     */
    public function isAvailable(\Magento\Quote\Api\Data\CartInterface $quote = null)
    {
        if ($quote) {
            $this->coreRegistry->register(RegistryConstants::CURRENT_STORE_ID, $quote->getStoreId(), true);
        }

        $storeCurrency = $this->storeManager->getStore()->getCurrentCurrency()->getCode();
        $xpcCurrency = $this->helper->settings->getXpcConfig('currency');

        return parent::isAvailable($quote)
            && $storeCurrency == $xpcCurrency
            && $this->helper->settings->isConfigured();
    }

    /**
     * Compose Transaction ID
     *
     * @param $txnId Numeric (unique) key of the transaction from X-Payments
     *
     * @return string
     */
    protected function composeTransactionId($txnId)
    {
        return sprintf('%s-%s', $this->paymentAction, $txnId);
    }

    /**
     * Calculate the close transaction flag
     *
     * @param array $paymentData Payment data from response
     *
     * @return bool
     */
    protected function getIsCloseTransaction($paymentData)
    {
        switch ($this->paymentAction) {

            case self::ACTION_AUTHORIZE:
            case self::ACTION_SALE:
            case self::ACTION_CAPTURE:
                $result = ($paymentData['capturedAmountAvail'] <= 0.001); // Floats
                break;

            default:
                $result = true;
        }

        return $result;
    }

    /**
     * Check transaction status
     *
     * @param array $paymentData Payment data from response
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     *
     * @return void
     */
    protected function checkTransactionStatus($paymentData)
    {
        $result = true;

        if (self::ACTION_AUTHORIZE == $this->paymentAction) {

            $result = (Api::AUTH_STATUS === (int)$paymentData['status']);

        } elseif (self::ACTION_SALE == $this->paymentAction) {

            $result = (Api::CHARGED_STATUS === (int)$paymentData['status']);
        }

        if (!$result) {

            throw new \Magento\Framework\Exception\LocalizedException(__('Transaction failed'));
        }
    }

    /**
     * Save Masked Card Data
     *
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @param array $paymentData Payment data from response
     *
     * @return void
     */
    protected function saveMaskedCardData(\Magento\Payment\Model\InfoInterface $payment, $paymentData)
    {
        $card = $this->paymentCardFactory->create()
            ->importFromResponse($paymentData, true);

        // Additional information (serialized)
        $payment->setAdditionalInformation('card_type', $card->getCardType());
        $payment->setAdditionalInformation('card_number', $card->getCardLabel());

        // Regular CC data
        $payment->setCcLast4($card->getLast4())
            ->setCcExpYear($card->getExpirationYear())
            ->setCcExpMonth($card->getExpirationMonth())
            ->setCcType($card->getCardType());

        // Save Payment Card
        if ('Y' == $paymentData['saveCard']) {

            $data = array(
                'user_id'    => $payment->getOrder()->getCustomerId(),
                'txnId'      => $paymentData['txnId'],
                'conf_id'    => $this->helper->settings->getXpcConfig('active_confid'),
                'store_id'   => $payment->getOrder()->getStoreId(),
                'address_id' => $payment->getOrder()->getBillingAddress()->getCustomerAddressId(),
            );

            $card->addData($data)->save();
        }
    }

    /**
     * Save Transaction Data
     *
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @param array $transactionData Transaction data from response
     *
     * @return void
     */
    protected function saveTransactionData(\Magento\Payment\Model\InfoInterface $payment, $transactionData)
    {
        // Raw transaction details
        if (!empty($transactionData['fields'])) {

            $data = array();

            foreach ($transactionData['fields'] as $field) {
                $data[$field['name']] = $field['value'];
            }

            $payment->setTransactionAdditionalInfo(
                \Magento\Sales\Model\Order\Payment\Transaction::RAW_DETAILS,
                $data
            );
        }

        // Additional information (serialized)
        $payment->setAdditionalInformation('xpc_message', $transactionData['message']);

        // Regular data
        $payment->setCcTransId($transactionData['txnid']);
    }

    /**
     * Execute and Process get_info request
     *
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @param string $txnId
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     *
     * @return void
     */
    protected function processGetInfo(\Magento\Payment\Model\InfoInterface $payment, $txnId)
    {
        $response = $this->helper->api->requestPaymentInfo($txnId, false, true);

        if ($response->getStatus()) {
            $paymentData = $response->getField('payment');
        } else {
            throw new \Magento\Framework\Exception\LocalizedException($response->getErrorPhrase('Unable to get transaction details'));
        }

        // Check status for initial transaction
        $this->checkTransactionStatus($paymentData);

        // Assemble additional data
        $additionalData = $paymentData['advinfo'];
        $additionalData['xpc_txnid'] = $txnId;

        // Set Magento's transaction ID
        if (!empty($additionalData['txn_id'])) {

            $transactionId = $this->composeTransactionId($additionalData['txn_id']);
            $isClose = $this->getIsCloseTransaction($paymentData);

            $payment->setTransactionId($transactionId)
                ->setIsTransactionClosed($isClose);

            unset($additionalData['txn_id']);

        } else {

            throw new \Magento\Framework\Exception\LocalizedException(__('Payment txn_id is missing'));
        }

        // Save additional data
        foreach ($additionalData as $key => $value) {
            if (!empty($value)) {
                $payment->setAdditionalInformation($key, $value);
            }
        }

        // Process CC details
        if (
            self::ACTION_AUTHORIZE == $this->paymentAction
            || self::ACTION_SALE == $this->paymentAction
        ) {
            $this->saveMaskedCardData($payment, $paymentData);
        }

        // Process transction raw details
        $transactions = $response->getField('transactions');
        $transactionData = end($transactions);
        $this->saveTransactionData($payment, $transactionData);
    }

    /**
     * Set the payment action to authorize
     *
     * @return string
     */
    public function getConfigPaymentAction()
    {
        // Because ACTION_AUTHORIZE_CAPTURE executes capture without auth
        return self::ACTION_AUTHORIZE;
    }

    /**
     * Get name of the initial payment action
     *
     * @return string
     */
    protected function getInitPaymentActionName()
    {
        $action = $this->helper->settings->getPaymentConfig('payment_action');

        if (self::ACTION_AUTHORIZE_CAPTURE == $action) {
            $action = self::ACTION_SALE;
        }

        return $action;
    }

    /**
     * Get whether it is possible to capture
     *
     * @return bool
     */
    public function canCapture()
    {
        return true;
    }

    /**
     * Get whether it is possible to capture
     *
     * @return bool
     */
    public function canRefund()
    {
        return true;
    }

    /**
     * Get whether it is possible to capture
     *
     * @return bool
     */
    public function canVoid()
    {
        return true;
    }

    /**
     * Get whether it is possible to capture
     *
     * @return bool
     */
    public function canCancel()
    {
        return true;
    }

    /**
     * Capture request
     *
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @param string $amount
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     *
     * @return $this
     */
    public function capture(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        if ($amount <= 0) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Invalid amount for capture.'));
        }

        $this->paymentAction = self::ACTION_CAPTURE;

        $errorPhrase = false;

        try {

            $payment->setAmount($amount);

            $txnId = $payment->getAdditionalInformation('xpc_txnid');

            $response = $this->helper->api->requestPaymentCapture($txnId, $amount);

            if ($response->getStatus()) {

                $this->processGetInfo($payment, $txnId);

            } else {

                $errorPhrase = $response->getErrorPhrase();
            }

        } catch (\Exception $exception) {

            $errorPhrase = __('Error in capture: %1', $exception->getMessage());
        }

        if ($errorPhrase) {
            throw new \Magento\Framework\Exception\LocalizedException($errorPhrase);
        }

        return $this;
    }

    /**
     * Refund request
     *
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @param string $amount
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     *
     * @return $this
     */
    public function refund(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        if ($amount <= 0) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Invalid amount for refund.'));
        }

        $this->paymentAction = self::ACTION_REFUND;

        $errorPhrase = false;

        try {

            $txnId = $payment->getAdditionalInformation('xpc_txnid');

            $response = $this->helper->api->requestPaymentRefund($txnId, $amount);

            if ($response->getStatus()) {

                $this->processGetInfo($payment, $txnId);

            } else {

                $errorPhrase = $response->getErrorPhrase();
            }

        } catch (\Exception $exception) {

            $errorPhrase = __('Error in refund: %1', $exception->getMessage());
        }

        if ($errorPhrase) {
            throw new \Magento\Framework\Exception\LocalizedException($errorPhrase);
        }

        return $this;
    }

    /**
     * Void request
     *
     * @param \Magento\Payment\Model\InfoInterface $payment
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     *
     * @return $this
     */
    public function void(\Magento\Payment\Model\InfoInterface $payment)
    {
        $this->paymentAction = self::ACTION_VOID;

        $errorPhrase = false;

        try {

            $txnId = $payment->getAdditionalInformation('xpc_txnid');

            $response = $this->helper->api->requestPaymentVoid($txnId);

            if ($response->getStatus()) {

                $this->processGetInfo($payment, $txnId);

            } else {

                $errorPhrase = $response->getErrorPhrase();
            }

        } catch (\Exception $exception) {

            $errorPhrase = __('Error in void: %1', $exception->getPhrase());
        }

        if ($errorPhrase) {
            throw new \Magento\Framework\Exception\LocalizedException($errorPhrase);
        }

        return $this;
    }

    /**
     * Cancel request
     *
     * @param \Magento\Payment\Model\InfoInterface $payment
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     *
     * @return $this
     */
    public function cancel(\Magento\Payment\Model\InfoInterface $payment)
    {
        return $this->void($payment);
    }
}
