<?php
/**
 * Created by:  Milan Simek
 * Company:     Plugin Company
 *
 * LICENSE: http://plugin.company/docs/magento-extensions/magento-extension-license-agreement
 *
 * YOU WILL ALSO FIND A PDF COPY OF THE LICENSE IN THE DOWNLOADED ZIP FILE
 *
 * FOR QUESTIONS AND SUPPORT
 * PLEASE DON'T HESITATE TO CONTACT US AT:
 *
 * SUPPORT@PLUGIN.COMPANY
 */
namespace PluginCompany\CustomerGroupSwitching\Setup;

class InstallSchema implements \Magento\Framework\Setup\InstallSchemaInterface
{
    /**
     *
     *
     * @param \Magento\Framework\Setup\SchemaSetupInterface $setup
     * @param \Magento\Framework\Setup\ModuleContextInterface $context
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(
        \Magento\Framework\Setup\SchemaSetupInterface $setup,
        \Magento\Framework\Setup\ModuleContextInterface $context
    ) {
        $installer = $setup;
        $installer->startSetup();


        $tableName = 'plugincompany_groupswitch';
        if (!$installer->tableExists($tableName)) {
            $table = $installer->getConnection()->newTable(
                $installer->getTable($tableName)
            )
                ->addColumn(
                    'rule_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    [
                        'identity' => true,
                        'nullable' => false,
                        'primary'  => true,
                        'unsigned' => true,
                    ],
                    'ID'
                )
                ->addColumn(
                    'name',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    ['nullable => false'],
                    'Rule name'
                )
                ->addColumn(
                    'old_customergroup',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    ['nullable' => true],
                    'old_customergroup'
                )
                ->addColumn(
                    'new_customergroup',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    ['nullable' => true],
                    'new_customergroup'
                )
                ->addColumn(
                    'from_date',
                    \Magento\Framework\DB\Ddl\Table::TYPE_DATE,
                    null,
                    ['nullable' => true],
                    'from_date'
                )
                ->addColumn(
                    'to_date',
                    \Magento\Framework\DB\Ddl\Table::TYPE_DATE,
                    null,
                    ['nullable' => true],
                    'to_date'
                )
                ->addColumn(
                    'is_active',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    1,
                    ['nullable' => false, 'default' => '0'],
                    'is_active'
                )
                ->addColumn(
                    'conditions_serialized',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    '64k',
                    ['nullable' => true],
                    'conditions_serialized'
                )
                ->addColumn(
                    'stop_rules_processing',
                    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    null,
                    ['nullable' => false, 'default' => 1],
                    'stop_rules_processing'
                )
                ->addColumn(
                    'sort_order',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['nullable' => true],
                    'sort_order'
                )
                ->addColumn(
                    'store_ids',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    ['nullable' => true],
                    'store_ids'
                )
                ->addColumn(
                    'order_status',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    ['nullable' => true],
                    'order_status'
                )
                ->addColumn(
                    'invoice_status',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    ['nullable' => true],
                    'invoice_status'
                )
                ->addColumn(
                    'executed_count',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false, 'default' => 0],
                    'executed_count'
                )
                ->addColumn(
                    'events',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    ['nullable' => true],
                    'events'
                )
                ->addColumn(
                    'customer_notification',
                    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    null,
                    ['nullable' => true, 'default' => 0],
                    'customer_notification'
                )
                ->addColumn(
                    'customer_notification_subject',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    ['nullable' => true, 'default' => null],
                    'customer_notification_subject'
                )
                ->addColumn(
                    'customer_notification_contents',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    '64k',
                    ['nullable' => true, 'default' => null],
                    'customer_notification_contents'
                )
                ->addColumn(
                    'admin_notification',
                    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    null,
                    ['nullable' => true, 'default' => 0],
                    'admin_notification'
                )
                ->addColumn(
                    'admin_notification_email',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    ['nullable' => true, 'default' => null],
                    'admin_notification_email'
                )
                ->addColumn(
                    'admin_notification_subject',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    ['nullable' => true, 'default' => null],
                    'admin_notification_subject'
                )
                ->addColumn(
                    'admin_notification_contents',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    '64k',
                    ['nullable' => true, 'default' => null],
                    'admin_notification_contents'
                )
                ->addColumn(
                    'apply_new_group_to_order',
                    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    null,
                    ['nullable' => true, 'default' => 0],
                    'apply_new_group_to_order'
                )
            ;
            $installer->getConnection()->createTable($table);
        }
        $installer->endSetup();
    }
}