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
 
namespace CDev\XPaymentsConnector\Helper;

use CDev\XPaymentsConnector\Controller\RegistryConstants;

/**
 * Helper for settings
 */
class Settings extends \CDev\XPaymentsConnector\Helper\AbstractHelper
{
    /**
     * List of tabs
     */
    const TAB_WELCOME         = 'welcome';
    const TAB_CONNECTION      = 'connection';
    const TAB_PAYMENT_METHODS = 'payment_methods';
    const TAB_ZERO_AUTH       = 'zero_auth';

    // Configuration settings path
    const XPATH_PAYMENT      = 'payment/xpc/';
    const XPATH_PAYMENT_CARD = 'payment/xpc_payment_card/';

    // Validation regexp for serialized bundle
    const BUNDLE_REGEXP = '/a:[56]:{s:8:"store_id";s:\d+:"[0-9a-z]+";s:3:"url";s:\d+:"[^"]+";s:10:"public_key";s:\d+:"-----BEGIN CERTIFICATE-----[^"]+-----END CERTIFICATE-----";s:11:"private_key";s:\d+:"-----BEGIN [A-Z ]*PRIVATE KEY-----[^"]+-----END [A-Z ]*PRIVATE KEY-----";s:20:"private_key_password";s:32:".{32}";(s:9:"server_ip";s:\d+:"[0-9a-fA-F\.:]*";)?}/s';

    // Requirements errors
    const REQ_CURL    = 'PHP extension cURL is not installed on your server';
    const REQ_OPENSSL = 'PHP extension OpenSSL is not installed on your server';
    const REQ_DOM     = 'PHP extension DOM is not installed on your server';

    // Configuration errors
    const CONF_CART_ID          = 'Store ID is empty or has an incorrect value';
    const CONF_URL              = 'X-Payments URL is empty or has an incorrect value';
    const CONF_PUBLIC_KEY       = 'Public key is empty';
    const CONF_PRIVATE_KEY      = 'Private key is empty';
    const CONF_PRIVATE_KEY_PASS = 'Private key password is empty';
    const CONF_API_VERSION      = 'API version is empty';

    /**
     * Disabled zero auth method index
     */
    const ZERO_AUTH_DISABLED = -1;

    /**
     * Config writer interface
     */
    protected $configWriter = null;

    /**
     * Cache type list (cleaned during update)
     */
    protected $cacheTypeList = null;

    /**
     * Config reader interface
     */
    protected $scopeConfig = null;

    /**
     * XPC config data factory
     */
    protected $configFactory = null;

    /**
     * Tab names
     */
    protected $tabs = array(
        self::TAB_WELCOME         => 'Welcome',
        self::TAB_CONNECTION      => 'Connection',
        self::TAB_PAYMENT_METHODS => 'Payment methods',
        self::TAB_ZERO_AUTH       => 'Saving payment cards',
    );

    /**
     * Translated tabs
     */
    private static $translatedTabs = null;

    /**
     * List of supported API versions
     */
    protected $apiVersions = array(
        '1.9',
        '1.7',
        '1.6',
    );

    /**
     * List of configuration errors
     */
    private static $configurationErrors = null;

    /**
     * List of server requirements errors
     */
    private static $requirementsErrors = null;

    /**
     * Flag indicating if at least one of the payment configurations allows to save cards
     */
    private static $canSaveCardsFlag = null;

    /**
     * Zero auth mehod model
     */
    private static $zeroAuthMethod = null;

    /**
     * Is it necessary to re-check connection
     */
    private static $recheckFlag = false;

    /**
     * Flag indicating configuration is in progress
     * To prevent concurent requests to X-Payments
     */
    private static $configurationInProgressFlag = false;

    /**
     * Map of fields stored in the bundle
     */
    protected $bundleMap = array(
        'store_id',
        'url',
        'public_key',
        'private_key',
        'private_key_password',
    );

    /**
     * Core registry
     */
    protected $coreRegistry = null;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\App\Config\Storage\WriterInterface $configWriter
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \CDev\XPaymentsConnector\Model\ConfigDataFactory $configFactory
     * @param \Magento\Framework\Registry $coreRegistry
     *
     * @return void
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\Config\Storage\WriterInterface $configWriter,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \CDev\XPaymentsConnector\Model\ConfigDataFactory $configFactory,
        \Magento\Framework\Registry $coreRegistry
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->configWriter = $configWriter;
        $this->cacheTypeList = $cacheTypeList;

        $this->configFactory = $configFactory;

        $this->coreRegistry = $coreRegistry;
    }

    /**
     * Get tabs (translated)
     *
     * @return array
     */
    public function getTabs()
    {
        if (null === static::$translatedTabs) {

            static::$translatedTabs = array();

            // Translate tabs
            foreach ($this->tabs as $tab => $tabName) {
                static::$translatedTabs[$tab] = __($tabName);
            }
        }

        return static::$translatedTabs;
    }

    /**
     * Get config option value common for all payment methods
     *
     * @param string $name Option name
     *
     * @return string (or whatever is there)
     */
    public function getXpcConfig($name)
    {
        $config = $this->configFactory->create()
            ->loadByName($name);

        $value = $config->getValue();

        return $value;
    }

    /**
     * Set config option value common for all payment methods
     *
     * @param string $name Option name
     * @param string $value Option value
     * @param bool   $flushCache Is it necessary to reload config
     *
     * @return void
     */
    public function setXpcConfig($name, $value, $flushCache = false)
    {
        $config = $this->configFactory->create()
            ->loadByName($name);

        if (empty($config->getName())) {

            $storeId = (int)$this->coreRegistry->registry(RegistryConstants::CURRENT_STORE_ID);

            $config->setName($name);
            $config->setStoreId($storeId);
        }

        $config->setValue($value)->save();

        if ($flushCache) {

            $this->flushCache();
        }
    }

    /**
     * Get core config option value
     *
     * @param string $path Option path in config
     *
     * @return string (or whatever is there)
     */
    protected function readCoreConfig($path)
    {
        $storeId = (int)$this->coreRegistry->registry(RegistryConstants::CURRENT_STORE_ID);

        if (0 !== $storeId) {

            $value = $this->scopeConfig->getValue($path, 'stores', $storeId);

        } else {

            $value = $this->scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        }

        return $value;
    }

    /**
     * Set core config option value
     *
     * @param string $path Option path in config
     * @param string $value Option value
     *
     * @return void
     */
    protected function writeCoreConfig($path, $value)
    {
        $storeId = (int)$this->coreRegistry->registry(RegistryConstants::CURRENT_STORE_ID);

        if (0 !== $storeId) {

            $this->configWriter->save($path, $value, 'stores', $storeId);

        } else {

            $this->configWriter->save($path, $value);
        }
    }

    /**
     * Get config option value for specific payment method
     *
     * @param string $name Option name
     *
     * @return string (or whatever is there)
     */
    public function getPaymentConfig($name)
    {
        $path = self::XPATH_PAYMENT . $name;

        return $this->readCoreConfig($path);
    }

    /**
     * Set config option value for specific payment method
     *
     * @param string $name Option name
     * @param string $value Option value
     *
     * @return void
     */
    public function setPaymentConfig($name, $value)
    {
        $path = self::XPATH_PAYMENT . $name;

        $this->writeCoreConfig($path, $value);
    }

    /**
     * Get config option value for saved payment cards payment method
     *
     * @param string $name Option name
     *
     * @return string (or whatever is there)
     */
    public function getPaymentCardConfig($name)
    {
        $path = self::XPATH_PAYMENT_CARD . $name;

        return $this->readCoreConfig($path);
    }

    /**
     * Set config option value for saved cards payment method
     *
     * @param string $name Option name
     * @param string $value Option value
     *
     * @return void
     */
    public function setPaymentCardConfig($name, $value)
    {
        $path = self::XPATH_PAYMENT_CARD . $name;

        $this->writeCoreConfig($path, $value);
    }

    /**
     * Decode bundle. It's base64 encoded
     * serialized or JSON-encoded array
     *
     * @param string $bundle If not passed use value from the config
     *
     * @return array
     */
    public function decodeBundle($bundle = null)
    {
        if (null === $bundle) {
            $bundle = $this->getXpcConfig('xpay_conf_bundle');
        }

        $decoded = base64_decode($bundle, true);

        if (false !== $decoded) {

            if (preg_match(self::BUNDLE_REGEXP, $decoded)) {
                // Try old-fashioned serialized bundle
                $result = @unserialize($decoded);
            } else {
                // Try modern JSON bundle
                $result = json_decode($decoded, true);
            }
        }

        if (empty($result) || !is_array($result)) {
            $result = array();
        }

        return $result;
    }

    /**
     * Cleanup bundled fields
     *
     * @return void
     */
    public function cleanXpcBundle()
    {
        foreach ($this->bundleMap as $name) {
            $this->setXpcConfig($name, '');
        }

        $this->setXpcConfig('api_version', '');

        $this->helper->logDebug('XPC bundle cleaned');
    }

    /**
     * Process and save bundle in the config
     *
     * @param $bundle Base64 encoded, serialized bundle
     *
     * @return void
     */
    public function setXpcBundle($bundle)
    {
        $decoded = $this->decodeBundle($bundle);

        foreach ($this->bundleMap as $name) {
            $value = !empty($decoded[$name]) ? $decoded[$name] : '';
            $this->setXpcConfig($name, $value);
        }
    }

    /**
     * Set flag to force the connection check.
     * And null other flags/cache just in case.
     *
     * Flag is reverted after the connection check is done.
     *
     * @return void
     */
    public function setRecheckFlag()
    {
        static::$configurationErrors = null;
        static::$requirementsErrors = null;
        static::$canSaveCardsFlag = null;
        static::$zeroAuthMethod = null;

        static::$recheckFlag = true;
    }

    /**
     * Check if X-Payments Connector module is configured
     *
     * @return bool
     */
    public function isConfigured()
    {
        // If configuration is in progress return true to avoid the concurent checking
        if (static::$configurationInProgressFlag) {
            return true;
        }

        if (static::$recheckFlag) {

            // Prevent concurent requests when configuration is in progress
            // E.g. do not obtain API version during payment confs import
            static::$configurationInProgressFlag = true;

            try {

                $result = false;

                if (!empty($this->getXpcConfig('store_id'))) {

                    $errors = $this->getConfigurationErrors();

                    if (empty($errors)) {
                        $result = true;
                    }
                }

                $this->setXpcConfig('is_configured', $result, true);

            } catch (\Exception $exception) {

                // Switch off configuration in progress flag for any exception
                static::$configurationInProgressFlag = false;

                // And throw exception further
                throw $exception;
            }

            // Switch off configuration checking flags
            static::$recheckFlag = false;
            static::$configurationInProgressFlag = false;
        }

        return $this->getXpcConfig('is_configured');
    }

    /**
     * Check connection with X-Payments via test request. Set the API version
     *
     * @return string Error message during communication with X-Payments (if any)
     */
    private function obtainApiVersion()
    {
        $isUpgradeNeeded = false;

        $errors = array();

        try {

            $this->setXpcConfig('api_version', '', true);

            foreach ($this->apiVersions as $version) {

                $response = $this->helper->api->testConnection($version);

                $errorCode = $response->getErrorCode();

                if ($response->getStatus()) {

                    $this->setXpcConfig('api_version', $version, true);
                    break;

                } elseif (!empty($errorCode)) {

                    if ('506' == $response->getErrorCode()) {
                        $isUpgradeNeeded = true;
                    }

                    $errors[] = $response->getErrorMessage();
                }
            }

            if (!$this->getXpcConfig('api_version')) {

                if ($isUpgradeNeeded) {
                    $error = 'Unable to detect supported API version for your X-Payments installation. You should upgrade X-Payments or X-Payments connector extension';
                } else {
                    $error = implode(PHP_EOL, $errors);
                }

            } else {

                $error = false;
            }

        } catch (\Exception $e) {

            $error = $e->getMessage();
        }

        return $error;
    }

    /**
     * Get configuration errors list
     *
     * @return array
     */
    public function getConfigurationErrors()
    {
        if (is_array(static::$configurationErrors)) {
            return static::$configurationErrors;
        }

        static::$configurationErrors = array();

        // Check shopping cart id
        if (
            !$this->getXpcConfig('store_id')
            || !preg_match('/^[\da-f]{32}$/Ss', $this->getXpcConfig('store_id'))
        ) {
            static::$configurationErrors[] = self::CONF_CART_ID;
        }

        // Check URL
        if (!$this->getXpcConfig('url')) {
            static::$configurationErrors[] = self::CONF_URL;
        }

        $parsed_url = @parse_url($this->getXpcConfig('url'));

        $forceHttp = $this->getXpcConfig('force_http');

        if (
            !$parsed_url
            || !isset($parsed_url['scheme'])
            || (
                $parsed_url['scheme'] != 'https'
                && !$forceHttp
            )
        ) {
            static::$configurationErrors[] = self::CONF_URL;
        }

        // Check public key
        if (!$this->getXpcConfig('public_key')) {
            static::$configurationErrors[] = self::CONF_PUBLIC_KEY;
        }

        // Check private key
        if (!$this->getXpcConfig('private_key')) {
            static::$configurationErrors[] = self::CONF_PRIVATE_KEY;
        }

        // Check private key password
        if (!$this->getXpcConfig('private_key_password')) {
            static::$configurationErrors[] = self::CONF_PRIVATE_KEY_PASS;
        }

        // Obtain API version from X-Payments if necessary
        if (static::$recheckFlag) {
            $error = $this->obtainApiVersion();
        }

        // Check API version
        if (!$this->getXpcConfig('api_version')) {
            static::$configurationErrors[] = !empty($error) ? $error : self::CONF_API_VERSION;
        }

        return static::$configurationErrors;
    }

    /**
     * Get list of the server requirements errors
     *
     * @return array
     */
    public function getRequirementsErrors()
    {
        if (is_array(static::$requirementsErrors)) {
            return static::$requirementsErrors;
        }

        static::$requirementsErrors = array();

        if (!function_exists('curl_init')) {
            static::$requirementsErrors[] = self::REQ_CURL;
        }

        if (
            !function_exists('openssl_pkey_get_public') || !function_exists('openssl_public_encrypt')
            || !function_exists('openssl_get_privatekey') || !function_exists('openssl_private_decrypt')
            || !function_exists('openssl_free_key')
        ) {
            static::$requirementsErrors[] = self::REQ_OPENSSL;
        }

        if (!class_exists('DOMDocument')) {
            static::$requirementsErrors[] = self::REQ_DOM;
        }

        return static::$requirementsErrors;
    }

    /**
     * Check - module requirements is passed or not
     *
     * @return bool
     */
    public function checkRequirements()
    {
        $errors = $this->getRequirementsErrors();

        return empty($errors);
    }

    /**
     * Get redirect form URL
     *
     * @return string
     */
    public function getPaymentUrl()
    {
        $url = $this->getXpcConfig('url');
        $url = rtrim($url, '/') . '/payment.php';

        return $url;
    }

    /**
     * Get admin URL
     *
     * @return string
     */
    public function getAdminUrl()
    {
        $url = $this->getXpcConfig('url');
        $url = rtrim($url, '/') . '/admin.php';

        return $url;
    }

    /**
     * Get admin URL
     *
     * @return string
     */
    public function getApiUrl()
    {
        $url = $this->getXpcConfig('url');
        $url = rtrim($url, '/') . '/api.php';

        return $url;
    }

    /**
     * Flush magento config and page cache
     *
     * @return void
     */
    public function flushCache()
    {
        $this->cacheTypeList->cleanType(\Magento\Framework\App\Cache\Type\Config::TYPE_IDENTIFIER);

        $this->cacheTypeList->cleanType(\Magento\PageCache\Model\Cache\Type::TYPE_IDENTIFIER);

        // TODO: Consider remove logging
        $this->helper->logDebug('Flush cache');
    }
}
