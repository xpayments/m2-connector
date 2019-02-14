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

namespace CDev\XPaymentsConnector\Block\Adminhtml\Button\PaymentCard;

use CDev\XPaymentsConnector\Controller\RegistryConstants;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Magento\Framework\UrlInterface;

/**
 * Add New Payment Card button
 */
class AddNew implements ButtonProviderInterface
{

    /**
     * URL Builder interface
     */
    protected $urlBuider = null;

    /**
     * Core registry
     */
    protected $coreRegistry = null;

    /**
     * Store manager
     */
    protected $storeManager = null;

    /**
     * Constructor
     *
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     *
     * @return void
     */
    public function __construct(
        UrlInterface $urlBuilder,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->coreRegistry = $coreRegistry;
        $this->storeManager = $storeManager;
    }

    /**
     * Get store ID
     *
     * @return int
     */
    protected function getStoreId()
    {
        $storeId = (int)$this->coreRegistry->registry(RegistryConstants::CURRENT_STORE_ID);

        if (!$storeId) {
            // Use default store if it's not set. Payment card cannot be added without store
            $storeId = $this->storeManager->getDefaultStoreView()->getId();
        }

        return $storeId;
    }

    /**
     * Get button data
     *
     * @return array
     */
    public function getButtonData()
    {
        $data = array();

        $customerId = $this->coreRegistry->registry(RegistryConstants::CURRENT_CUSTOMER_ID);

        if ($customerId) {

            $storeId = $this->getStoreId();

            $params = array(
                'user_id' => $customerId,
                'store'   => $storeId, // Use Magento's `store` for store-switcher; TODO: Correct store-switcher
            );

            $url = $this->urlBuilder->getUrl('xpc/payment_card/add', $params);

            $data = array(
                'label'      => __('Add Payment Card'),
                'on_click'   => sprintf('location.href = "%s";', $url),
                'class'      => 'primary',
                'sort_order' => 40,
            );
        }

        return $data;
    }
}
