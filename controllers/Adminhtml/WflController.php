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
 * Class Ivoinov_Wfl_Adminhtml_WflController
 */
class Ivoinov_Wfl_Adminhtml_WflController extends Mage_Adminhtml_Controller_Action
{
    public function sendAction()
    {
        $orderId = $this->getRequest()->getParam('order_id', null);
        if ($orderId !== null) {
            $order = Mage::getModel('sales/order')->load($orderId);
            if ($order && $order->getId()) {
                /** @var Ivoinov_Wfl_Model_Export_Order $exportModel */
                $exportModel = Mage::getModel('ivoinov_wfl/export_order');
                $exportModel->exportOrder($order);
                $this->_getSession()->addSuccess($this->__('Order has been send to FTP'));
                $this->_redirect('*/sales_order');
            } else {
                $this->_getSession()->addError($this->__('This order no longer exists.'));
                $this->_redirect('*/sales_order');
            }
        } else {
            $this->_getSession()->addError($this->__('This order no longer exists.'));
            $this->_redirect('*/sales_order');
        }
    }
}