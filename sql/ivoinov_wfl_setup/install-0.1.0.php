<?php
/**
 * PHP version 5
 *
 * LICENSE: This source file is subject to version 3.01 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_01.txt.  If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 * @category   CategoryName
 * @package    PackageName
 * @author     Ilya Voinov <ilya.voinov@yahoo.com>
 * @copyright  1997-2016 The PHP Group
 * @license    http://www.php.net/license/3_01.txt  PHP License 3.01
 */
/* @var $installer Mage_Sales_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();
$installer->addAttribute(Mage_Sales_Model_Order::ENTITY, Ivoinov_Wfl_Helper_Data::ORDER_ATTRIBUTE_CODE_IS_SEND_TO_WFL,
    array('type' => 'int', 'visible' => true, 'required' => false, 'length' => 6));
$installer->getConnection()->addColumn($installer->getTable('sales/order_grid'),
    Ivoinov_Wfl_Helper_Data::ORDER_ATTRIBUTE_CODE_IS_SEND_TO_WFL, array(
        'type'     => Varien_Db_Ddl_Table::TYPE_SMALLINT,
        'unsigned' => true,
        'nullable' => false,
        'default'  => '0',
        'comment' => 'Is send to WILLIAMS FASHION LOGISTICS flag'
    ));
$installer->endSetup();