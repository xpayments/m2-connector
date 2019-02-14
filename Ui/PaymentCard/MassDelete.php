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

namespace CDev\XPaymentsConnector\Ui\PaymentCard;

/**
 * Mass delete action component
 */
class MassDelete extends \Magento\Ui\Component\Action
{
    /**
     * URL Builder
     */
    protected $urlBuilder = null;
 
    /**
     * Request
     */
    protected $request = null;

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\UiComponent\ContextInterface $context
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     * @param array|JsonSerializable $actions
     *
     * @return void
     */
    public function __construct(
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\UrlInterface $urlBuilder,
        array $components = array(),
        array $data = array(),
        $actions = null
    ) {
        parent::__construct($context, $components, $data, $actions);

        $this->urlBuilder = $urlBuilder;
        $this->request = $request;
    }
 
    /**
     * Prepare component configuration
     *
     * @return void
     */
    public function prepare()
    {
        parent::prepare();

        $params = array();

        $userId = $this->request->getParam('user_id');

        if ($userId) {
            $params['user_id'] = $userId;
        }

        $storeId = $this->request->getParam('store_id');

        if ($storeId) {
            $params['store_id'] = $storeId;
        }

        $config = $this->getConfiguration();

        $config['url'] = $this->urlBuilder->getUrl($config['urlPath'], $params);

        $this->setData('config', $config);
    }
}
