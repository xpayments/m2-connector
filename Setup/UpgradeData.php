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

namespace CDev\XPaymentsConnector\Setup;

/**
 * Class \Magento\SalesRule\Setup\UpgradeData
 */
class UpgradeData implements \Magento\Framework\Setup\UpgradeDataInterface
{
    /**
     * @inheritdoc
     */
    public function upgrade(
        \Magento\Framework\Setup\ModuleDataSetupInterface $setup,
        \Magento\Framework\Setup\ModuleContextInterface $context
    ) {

        $installer = $setup;
        $installer->startSetup();

        if (version_compare($context->getVersion(), '1.0.1', '<')) {

            $this->setDefaultZeroAuthData($installer);
        }

        $installer->endSetup();
    }

    /**
     * Set default Zero-Auth data
     * 1.0.0 => 1.1.1
     *
     * @param \Magento\Framework\Setup\ModuleDataSetupInterface $installer
     *
     * @return void
     */
    protected function setDefaultZeroAuthData(\Magento\Framework\Setup\ModuleDataSetupInterface $installer)
    {
        $tableName = $installer->getTable('xpc_config_data');

        // Check if the table already exists
        if ($installer->getConnection()->isTableExists($tableName)) {


            $data = array(
                array(
                    'name'  => 'zero_auth_amount',
                    'value' => '1.00',
                ),
                array(
                    'name'  => 'zero_auth_description',
                    'value' => 'Card Setup',
                ),
            );

            foreach ($data as $row) {
                $installer->getConnection()->insert($tableName, $row);
            } 
        }
    }
}
