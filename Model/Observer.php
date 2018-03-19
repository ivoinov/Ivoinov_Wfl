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

/**
 * Class Ivoinov_Wfl_Model_Observer
 */
class Ivoinov_Wfl_Model_Observer
{
    /**
     * @param Mage_Cron_Model_Schedule|null $schedule
     */
    public function exportOrders(Mage_Cron_Model_Schedule $schedule = null)
    {
        /** @var Ivoinov_Wfl_Model_Export_Order $exportModel */
        $exportModel = Mage::getModel('ivoinov_wfl/export_order');
        $exportModel->export();
    }

    /**
     * @param Mage_Cron_Model_Schedule|null $schedule
     */
    public function importOrdersStatus(Mage_Cron_Model_Schedule $schedule = null)
    {
        /** @var Ivoinov_Wfl_Model_Import_Order $importModel */
        $importModel = Mage::getModel('ivoinov_wfl/import_order');
        $importModel->import();
    }

    /**
     * Add additional columns to order grid.
     *
     * @param Varien_Event_Observer $observer
     *
     * @throws Exception
     */
    public function adminhtmlAddColumnsToOrderGrid(Varien_Event_Observer $observer)
    {
        $block = $observer->getEvent()->getBlock();
        if ($block instanceof Mage_Adminhtml_Block_Sales_Order_Grid) {
            /** @var Mage_Adminhtml_Block_Sales_Order_Grid $block */
            $block->addColumn(Ivoinov_Wfl_Helper_Data::ORDER_ATTRIBUTE_CODE_IS_SEND_TO_WFL, array(
                'header'  => Mage::helper('sales')->__('Is sent to warehouse'),
                'index'   => Ivoinov_Wfl_Helper_Data::ORDER_ATTRIBUTE_CODE_IS_SEND_TO_WFL,
                'type'    => 'options',
                'options' => Mage::getModel('adminhtml/system_config_source_yesno')->toArray(),
                'width'   => '70px',
            ));
            $block->addColumnsOrder(Ivoinov_Wfl_Helper_Data::ORDER_ATTRIBUTE_CODE_IS_SEND_TO_WFL, 'grand_total');

            $block->addColumn(Ivoinov_Wfl_Helper_Data::ORDER_ATTRIBUTE_IS_SEND_TO_WFL_DATE, array(
                'header' => Mage::helper('sales')->__('Sent to warehouse at'),
                'index'  => Ivoinov_Wfl_Helper_Data::ORDER_ATTRIBUTE_IS_SEND_TO_WFL_DATE,
                'type'   => 'datetime',
            ));
            $block->addColumnsOrder(Ivoinov_Wfl_Helper_Data::ORDER_ATTRIBUTE_IS_SEND_TO_WFL_DATE,
                Ivoinov_Wfl_Helper_Data::ORDER_ATTRIBUTE_CODE_IS_SEND_TO_WFL);
            $block->sortColumnsByOrder();
        }
    }
}