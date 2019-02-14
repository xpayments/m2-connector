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

namespace CDev\XPaymentsConnector\Ui\PaymentConfiguration;

/**
 * Data provider for the payment configuration grid
 */
class DataProvider extends \Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider
{
    /**
     * XPC Helper
     */
    protected $helper = null;

    /**
     * Constructor
     *
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param \Magento\Framework\Api\Search\ReportingInterface $reporting
     * @param \Magento\Framework\Api\Search\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\Api\FilterBuilder $filterBuilder
     * @param \CDev\XPaymentsConnector\Helper\Data $helper
     * @param array $meta
     * @param array $data
     *
     * @return void
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        \Magento\Framework\Api\Search\ReportingInterface $reporting,
        \Magento\Framework\Api\Search\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Api\FilterBuilder $filterBuilder,
        \CDev\XPaymentsConnector\Helper\Data $helper,
        array $meta = array(),
        array $data = array()
    ) {
        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $reporting,
            $searchCriteriaBuilder,
            $request,
            $filterBuilder,
            $meta,
            $data
        );

        $this->helper = $helper;
    }

    /**
     * Prepare update URL
     * see https://magento.stackexchange.com/questions/155309/
     *
     * @return void
     */
    protected function prepareUpdateUrl()
    {
        $storeId = (int)$this->request->getParam('store', 0);

        $this->data['config']['update_url'] = str_replace('PLACEHOLDER', $storeId, $this->data['config']['update_url']);
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        $data = parent::getData();

        $activeConfId = $this->helper->settings->getXpcConfig('active_confid');

        foreach ($data['items'] as & $item) {

            // KO needs currently selected element value
            $item['active_confid'] = $activeConfId;

            // Convert to boolean here, not in javascript
            $item['save_cards'] = !empty($item['save_cards']);
            $item['can_save_cards'] = !empty($item['can_save_cards']);
        }

        return $data;
    }
}
