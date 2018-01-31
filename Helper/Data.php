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

namespace Cdev\XPaymentsConnector\Helper;

/**
 * X-Payments Connector helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Current URL
     */
    private $url;

    /**
     * Logger
     */
    protected $logger = null;

    /**
     * Specific helpers.
     * If you keep the alphabetical order nothing is lost.
     */
    public $address = null;
    public $api = null;
    public $cart = null;
    public $settings = null;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Cdev\XPaymentsConnector\Logger\Logger $logger
     * @param \Cdev\XPaymentsConnector\Helper\Api $api
     * @param \Cdev\XPaymentsConnector\Helper\Address $address
     * @param \Cdev\XPaymentsConnector\Helper\Cart $cart
     * @param \Cdev\XPaymentsConnector\Helper\Settings $settings
     *
     * @return void
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Cdev\XPaymentsConnector\Logger\Logger $logger,
        \Cdev\XPaymentsConnector\Helper\Api $api,
        \Cdev\XPaymentsConnector\Helper\Address $address,
        \Cdev\XPaymentsConnector\Helper\Cart $cart,
        \Cdev\XPaymentsConnector\Helper\Settings $settings
    ) {
        parent::__construct($context);

        // Remove session key from URL
        $this->url = preg_replace(
            array('/\/key\/\w+\//', '/\?.*$/'),
            array('\/', ''),
            $storeManager->getStore()->getCurrentUrl()
        );

        $this->logger = $logger;

        $this->api = $api->setHelper($this);
        $this->address = $address->setHelper($this);
        $this->cart = $cart->setHelper($this);
        $this->settings = $settings->setHelper($this);
    }

    /**
     * Format price in 1234.56 format
     *
     * @param mixed $price
     *
     * @return string
     */
    public function preparePrice($price)
    {
        return number_format($price, 2, '.', '');
    }

    /**
     * Write interesting events to the log
     *
     * @param string $title Log title
     * @param mixed  $data  Data to log
     *
     * @return void
     **/
    public function logInfo($title, $data = '')
    {
        if (!is_string($data)) {
            $data = var_export($data, true);
        }

        $message = PHP_EOL . date('Y-m-d H:i:s') . PHP_EOL
            . $title . PHP_EOL
            . $data . PHP_EOL
            . $this->url . PHP_EOL;

        $this->logger->info($message);
    }

    /**
     * Write detailed debug information to the log
     *
     * @param string $title Log title
     * @param mixed  $data  Data to log
     *
     * @return void
     **/
    public function logDebug($title, $data = '')
    {
        if (!is_string($data)) {
            $data = var_export($data, true);
        }

        $message = PHP_EOL . date('Y-m-d H:i:s') . PHP_EOL
            . $title . PHP_EOL
            . $data . PHP_EOL
            . $this->url . PHP_EOL;

        $this->logger->debug($message);
    }
}
