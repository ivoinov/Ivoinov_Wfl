<?php
/**
 * *
 *  * PHP version 5
 *  *
 *  * LICENSE: This source file is subject to version 3.01 of the PHP license
 *  * that is available through the world-wide-web at the following URI:
 *  * http://www.php.net/license/3_01.txt.  If you did not receive a copy of
 *  * the PHP License and are unable to obtain it through the web, please
 *  * send a note to license@php.net so we can mail you a copy immediately.
 *  *
 *  * @category   CategoryName
 *  * @package    PackageName
 *  * @author     Ilya Voinov <ilya.voinov@yahoo.com>
 *  * @copyright  1997-2016 The PHP Group
 *  * @license    http://www.php.net/license/3_01.txt  PHP License 3.01
 *
 */

/**
 * Class Ivoinov_Wfl_Helper_Statuses
 */
class Ivoinov_Wfl_Helper_Statuses extends Mage_Core_Helper_Abstract
{
    CONST WAREHOUSE_PROCESSING               = 'warehouse_processing';
    CONST WAREHOUSE_ORDER_RECEIVED           = 'warehouse_order_received';
    CONST WAREHOUSE_ORDER_QUEUED_FOR_PICKING = 'warehouse_queued_for_picking';
    CONST WAREHOUSE_ORDER_FULLY_PICKED       = 'warehouse_fully_picked';
    CONST WAREHOUSE_ORDER_PARTIALLY_PICKED   = 'warehouse_partially_picked';
    CONST WAREHOUSE_ORDER_FULLY_DISPATCHED   = 'warehouse_fully_dispatched';

    CONST DELIVERY_ORDER_RECEIVED                = '1101';
    CONST DELIVERY_ORDER_QUEUED_FOR_PICKING      = '1102';
    CONST DELIVERY_ORDER_FULLY_PICKED            = '1202';
    CONST DELIVERY_ORDER_PARTIALLY_PICKED        = '1201';
    CONST DELIVERY_ORDER_STATUS_PICKED_UP_FULL   = '1302';
    CONST DELIVERY_ORDER_STATUS_PICKED_PARTIALLY = '1301';

    /**
     * Warehouse order status VS magento order status
     *
     * @var array
     */
    static protected $_statusMap
        = array(
            self::DELIVERY_ORDER_STATUS_PICKED_UP_FULL   => Mage_Sales_Model_Order::STATE_COMPLETE,
            self::DELIVERY_ORDER_STATUS_PICKED_PARTIALLY => Mage_Sales_Model_Order::STATE_COMPLETE,
            self::DELIVERY_ORDER_RECEIVED                => self::WAREHOUSE_ORDER_RECEIVED,
            self::DELIVERY_ORDER_QUEUED_FOR_PICKING      => self::WAREHOUSE_ORDER_QUEUED_FOR_PICKING,
            self::DELIVERY_ORDER_FULLY_PICKED            => self::WAREHOUSE_ORDER_FULLY_PICKED,
            self::DELIVERY_ORDER_PARTIALLY_PICKED        => self::WAREHOUSE_ORDER_FULLY_DISPATCHED,
        );

    /**
     * Return magento order status via $warehouseStatus code
     *
     * @param $warehouseStatus
     *
     * @return mixed|string
     */
    public static function getMagentoOrderStatusByWarehouseStatus($warehouseStatus)
    {
        return isset(self::$_statusMap[$warehouseStatus]) ? self::$_statusMap[$warehouseStatus]
            : Mage_Sales_Model_Order::STATE_PROCESSING;
    }
}