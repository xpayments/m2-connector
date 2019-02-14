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

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

/**
 * Upgrade Schema
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * Upgrader
     *
     * @return void
     */
    public function upgrade(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {
 
        $installer = $setup;
        $installer->startSetup();

        if (version_compare($context->getVersion(), '1.0.1', '<')) {

            $this->createPaymentCardsTable($installer);
            $this->addStoreId($installer);
        }

        $installer->endSetup();
    }

    /**
     * Add Store ID to configuration tables
     * 1.0.0 => 1.1.1
     *
     * @param SchemaSetupInterface $installer
     *
     * @return void
     */
    protected function addStoreId(SchemaSetupInterface $installer)
    {
        $tableName = $installer->getTable('xpc_payment_configuration');

        // Check if the table already exists
        if ($installer->getConnection()->isTableExists($tableName)) {

            $installer->getConnection()->addColumn(
                $tableName,
                'store_id',
                array(
                    'type'     => Table::TYPE_INTEGER,
                    'nullable' => false,
                    'default'  => '0',
                    'comment'  => 'Store ID',
                    'after'    => 'confid',
                )
            );
        }

        $tableName = $installer->getTable('xpc_config_data');

        // Check if the table already exists
        if ($installer->getConnection()->isTableExists($tableName)) {

            $installer->getConnection()->addColumn(
                $tableName,
                'store_id',
                array(
                    'type'     => Table::TYPE_INTEGER,
                    'nullable' => false,
                    'default'  => '0',
                    'comment'  => 'Store ID',
                    'after'    => 'value',
                )
            );
        }
    }

    /**
     * Create payment cards table
     * 1.0.0 => 1.1.1
     *
     * @param SchemaSetupInterface $installer
     *
     * @return void
     */
    protected function createPaymentCardsTable(SchemaSetupInterface $installer)
    {
        $tableName = $installer->getTable('xpc_payment_card');

        // Check if the table already exists
        if ($installer->getConnection()->isTableExists($tableName) != true) {

            $table = $installer->getConnection()
                ->newTable($tableName)
                ->addColumn(
                    'id',
                    Table::TYPE_INTEGER,
                    null,
                    array(
                        'identity' => true,
                        'unsigned' => true,
                        'nullable' => false,
                        'primary'  => true,
                    )
                )
                ->addColumn(
                    'user_id',
                    Table::TYPE_INTEGER,
                    null,
                    array(
                        'nullable' => false,
                        'default'  => '0',
                    )
                )
                ->addColumn(
                    'conf_id',
                    Table::TYPE_INTEGER,
                    null,
                    array(
                        'nullable' => false,
                        'default'  => '0',
                    )
                )
                ->addColumn(
                    'store_id',
                    Table::TYPE_INTEGER,
                    null,
                    array(
                        'nullable' => false,
                        'default'  => '0',
                    )
                )
                ->addColumn(
                    'address_id',
                    Table::TYPE_INTEGER,
                    null,
                    array(
                        'nullable' => false,
                        'default'  => '0',
                    )
                )
                ->addColumn(
                    'card_type',
                    Table::TYPE_TEXT,
                    16,
                    array(
                        'nullable' => false,
                        'default'  => '',
                    )
                )
                ->addColumn(
                    'first6',
                    Table::TYPE_TEXT,
                    6,
                    array(
                        'nullable' => false,
                        'default'  => '',
                    )
                )
                ->addColumn(
                    'last4',
                    Table::TYPE_TEXT,
                    4,
                    array(
                        'nullable' => false,
                        'default'  => '',
                    )
                )
                ->addColumn(
                    'expiration_month',
                    Table::TYPE_TEXT,
                    2,
                    array(
                        'nullable' => false,
                        'default'  => '',
                    )
                )
                ->addColumn(
                    'expiration_year',
                    Table::TYPE_TEXT,
                    4,
                    array(
                        'nullable' => false,
                        'default'  => '',
                    )
                )
                ->addColumn(
                    'txnId',
                    Table::TYPE_TEXT,
                    255,
                    array(
                        'nullable' => false,
                        'default'  => '',
                    )
                )
                // Fields that Magento expects to find in a model. While not strictly required,
                // having these fields in your models is always a good idea.
                // http://alanstorm.com/magento_2_crud_models_for_database_access/
                ->addColumn(
                    'creation_time',
                    Table::TYPE_TIMESTAMP,
                    null,
                    array()
                )->addColumn(
                    'update_time',
                    Table::TYPE_TIMESTAMP,
                    null,
                    array()
                )
                ->setComment('XPC payment card table')
                ->setOption('type', 'InnoDB')
                ->setOption('charset', 'utf8');

            $installer->getConnection()->createTable($table);
        }
    }
}
