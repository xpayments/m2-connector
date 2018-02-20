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

namespace XPay\XPaymentsConnector\Model\ResourceModel;

/**
 * XPC quote data model resource
 */
class QuoteData extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('xpc_quote_data', 'id');
    }

    /**
     * Load quote data by quote_id and conf_id
     *
     * @param \XPay\XPaymentsConnector\Model\QuoteData $model
     * @param string $name Name
     *
     * @return int
     */
    public function loadByConf(\XPay\XPaymentsConnector\Model\QuoteData $model, $quoteId, $confId)
    {
        $table = $this->getMainTable();
        $connection = $this->getConnection();

        $select = $connection->select()->from($table, array('id'))->where('quote_id = ?', $quoteId)->where('conf_id = ?', $confId);

        $id = $connection->fetchOne($select);

        return $id;
    }
}
