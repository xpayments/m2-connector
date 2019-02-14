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

namespace CDev\XPaymentsConnector\Ui\Xpc;

use Magento\Checkout\Model\ConfigProviderInterface;
use CDev\XPaymentsConnector\Model\Payment\Method\Xpc;

/**
 * Configuration provider for XPC method at checkout
 */
class ConfigProvider implements ConfigProviderInterface
{
    /**
     * URL Builder
     */
    protected $urlBuilder = null;

    /**
     * Payment method object
     */
    protected $method = null;

    /**
     * Constructor
     *
     * @param \Magento\Payment\Helper\Data $paymentHelper
     * @param \Magento\Framework\UrlInterface $urlBuilder
     *
     * @return void
     */
    public function __construct(
        \Magento\Payment\Helper\Data $paymentHelper,
        \Magento\Framework\UrlInterface $urlBuilder
    ) {
        $this->urlBuilder = $urlBuilder;

        $this->method = $paymentHelper->getMethodInstance(Xpc::CODE);
    }

    /**
     * Get URL for iframe redirect
     *
     * @param bool $dropToken Is it necessary to drop current token
     * @param array $params URL params
     *
     * @return string
     */
    protected function getRedirectUrl($dropToken = false, $params = array())
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
                Xpc::CODE => array(
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
