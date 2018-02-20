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

namespace XPay\XPaymentsConnector\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;
use XPay\XPaymentsConnector\Gateway\Http\Client\ClientMock;

/**
 * Class ConfigProvider
 */
final class ConfigProvider implements ConfigProviderInterface
{
    /**
     * Payment method code
     */
    const CODE = 'xpc';

    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $urlBuilder;

    /**
     * Constructor
     *
     * @param \Magento\Framework\UrlInterface $urlBuilder
     *
     * @return void
     */
    public function __construct(
        \Magento\Framework\UrlInterface $urlBuilder
    ) {
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * Get URL for iframe redirect
     *
     * @param bool $dropToken Is it necessary to drop current token
     * @param array $params URL params
     *
     * @return string
     */
    private function getRedirectUrl($dropToken = false, $params = array())
    {
        if ($dropToken) {
            $params['drop_token'] = '1';
        }

        return $this->urlBuilder->getUrl('xpc/processing/redirect', $params);
    }

    /**
     * Retrieve assoc array of checkout configuration
     *
     * @return array
     */
    public function getConfig()
    {
        return array(
            'payment' => array(
                self::CODE => array(
                    'url' => array(
                        'redirect'             => $this->getRedirectUrl(),
                        'dropTokenAndRedirect' => $this->getRedirectUrl(true),
                        'saveCheckoutData'     => $this->urlBuilder->getUrl('xpc/processing/saveCheckoutData'),
                    ),
                ),
            ),
        );
    }
}
