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
 * Class Ivoinov_Wfl_Helper_Data
 */
class Ivoinov_Wfl_Helper_Data extends Mage_Core_Helper_Abstract
{
    CONST ORDER_ATTRIBUTE_CODE_IS_SEND_TO_WFL = 'is_send_to_wfl';
    CONST ORDER_ATTRIBUTE_IS_SEND_TO_WFL_DATE = 'send_to_wfl_at';
    CONST ORDER_WFL_FILE_CONTENT              = 'wfl_file_content';

    CONST DELIVERY_TAX_PERCENT                = 10;

    public function getDeliveryTaxPercent()
    {
        return self::DELIVERY_TAX_PERCENT;
    }

    public function getDeliveryTaxAmount($subtotalAmount)
    {
        return number_format(($subtotalAmount / 100) * self::DELIVERY_TAX_PERCENT, 2);
    }
}