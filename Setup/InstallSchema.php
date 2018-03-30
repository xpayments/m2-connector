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

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

/**
 * Install Schema
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * Installator
     *
     * @return void
     */
    public function install(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $installer = $setup;
        $installer->startSetup();

        $tableName = $installer->getTable('xpc_payment_configuration');
            
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
                    'confid',
                    Table::TYPE_INTEGER,
                    null,
                    array(
                        'nullable' => false,
                        'default'  => 0,
                    )
                )
                ->addColumn(
                    'name',
                    Table::TYPE_TEXT,
                    null,
                    array(
                        'nullable' => false,
                        'default'  => '',
                    )
                )
                ->addColumn(
                    'module',
                    Table::TYPE_TEXT,
                    null,
                    array(
                        'nullable' => false,
                        'default'  => '',
                    )
                )
                ->addColumn(
                    'hash',
                    Table::TYPE_TEXT,
                    null,
                    array(
                        'nullable' => false,
                        'default'  => '',
                    )
                )
                ->addColumn(
                    'currency',
                    Table::TYPE_TEXT,
                    3,
                    array(
                        'nullable' => false,
                        'default'  => '',
                    )
                )
                ->addColumn(
                    'payment_method_data',
                    Table::TYPE_TEXT,
                    null,
                    array(
                        'nullable' => false,
                        'default'  => false,
                    )
                )
                ->addColumn(
                    'is_active',
                    Table::TYPE_BOOLEAN,
                    null,
                    array(
                        'nullable' => false,
                        'default'  => false,
                    )
                )
                ->addColumn(
                    'save_cards',
                    Table::TYPE_BOOLEAN,
                    null,
                    array(
                        'nullable' => false,
                        'default'  => false,
                    )
                )
                ->addColumn(
                    'can_save_cards',
                    Table::TYPE_BOOLEAN,
                    null,
                    array(
                        'nullable' => false,
                        'default'  => false,
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
                ->setComment('Payment Configurations Table')
                ->setOption('type', 'InnoDB')
                ->setOption('charset', 'utf8');

            $installer->getConnection()->createTable($table);
        }

        $tableName = $installer->getTable('xpc_config_data');

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
                    'name',
                    Table::TYPE_TEXT,
                    null,
                    array(
                        'nullable' => false,
                        'default'  => '',
                    )
                )
                ->addColumn(
                    'value',
                    Table::TYPE_TEXT,
                    null,
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
                ->setComment('XPC config data table')
                ->setOption('type', 'InnoDB')
                ->setOption('charset', 'utf8');

            $installer->getConnection()->createTable($table);
        }

        $tableName = $installer->getTable('xpc_quote_data');

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
                    'quote_id',
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
                    'token',
                    Table::TYPE_TEXT,
                    null,
                    array(
                        'nullable' => false,
                        'default'  => '',
                    )
                )
                ->addColumn(
                    'txnid',
                    Table::TYPE_TEXT,
                    null,
                    array(
                        'nullable' => false,
                        'default'  => '',
                    )
                )
                ->addColumn(
                    'expires',
                    Table::TYPE_TIMESTAMP,
                    null,
                    array(
                        'nullable' => false,
                        'default'  => 0,
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
                ->setComment('XPC quote data table')
                ->setOption('type', 'InnoDB')
                ->setOption('charset', 'utf8');

            $installer->getConnection()->createTable($table);
        }
            
        $installer->endSetup();
    }
}
