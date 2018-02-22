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
 * Class Ivoinov_Wfl_Model_Export_Order
 */
class Ivoinov_Wfl_Model_Export_Order extends Ivoinov_Wfl_Model_Export
{
    CONST PATH_TO_FILE_ON_FTP = 'New_Orders/New/';

    /**
     * {@inheritdoc}
     */
    public function export()
    {
        /** @var Mage_Sales_Model_Order $order */
        foreach ($this->_getOrderCollectionForExport() as $order) {
            try {
                $orderXml = new DOMDocument('1.0', 'UTF-8');
                $ordersNode = $orderXml->appendChild($orderXml->createElement('Orders'));
                $orderNode = $ordersNode->appendChild($orderXml->createElement('Order'));
                $header = $orderNode->appendChild($orderXml->createElement('header'));
                $header->appendChild($orderXml->createElement('IsBackOrder', 'N'));
                $header->appendChild($orderXml->createElement('invoiceid', $order->getIncrementId()));
                $header->appendChild($orderXml->createElement('orderno', $order->getIncrementId()));
                $this->_addShippingInfo($header, $orderXml, $order);
                $this->_addBillingInfo($header, $orderXml, $order);
                $this->_addDeliveryInfo($header, $orderXml, $order);

                $products = $orderNode->appendChild($orderXml->createElement('products'));
                /** @var Mage_Sales_Model_Order_Item $orderItem */
                foreach ($order->getAllVisibleItems() as $orderItem) {
                    if ($orderItem->getProduct()->isConfigurable()) {
                        foreach ($orderItem->getChildrenItems() as $childOrderItem) {
                            $this->_addProductInfo($products, $orderXml, $order, $childOrderItem);
                        }
                    } else {
                        $this->_addProductInfo($products, $orderXml, $order, $orderItem);
                    }
                }
                $this->_addPaymentInfo($ordersNode, $orderXml, $order);
                $ordersNode->appendChild($orderXml->createElement('promotions'));
                $ordersNode->appendChild($orderXml->createElement('Status', 'CREATED'));
                $orderXml->formatOutput = true;
                $this->_downloadFileToFTP($orderXml, $order);
                $order->setData(Ivoinov_Wfl_Helper_Data::ORDER_ATTRIBUTE_CODE_IS_SEND_TO_WFL, 1);
                $order->save();
            } catch (Exception $e) {
                Mage::logException($e);
                continue;
            }
        }
    }

    /**
     * @return Mage_Sales_Model_Resource_Collection_Abstract
     */
    protected function _getOrderCollectionForExport()
    {
        return Mage::getResourceModel('sales/order_collection')
            ->addFieldToFilter(Ivoinov_Wfl_Helper_Data::ORDER_ATTRIBUTE_CODE_IS_SEND_TO_WFL,
                array(array('eq' => 0), array('null' => true)));
    }

    /**
     * @param DOMNode                $headerNode
     * @param DOMDocument            $document
     * @param Mage_Sales_Model_Order $order
     */
    protected function _addShippingInfo(
        DOMNode $headerNode,
        DOMDocument $document,
        Mage_Sales_Model_Order $order
    ) {
        /** @var Ivoinov_Wfl_Model_Export_ShippingInfo $shippingInfoModel */
        $shippingInfoModel = Mage::getModel('ivoinov_wfl/export_shippingInfo',
            array('order' => $order, 'document' => $document, 'node' => $headerNode));
        $shippingInfoModel->process();
    }

    /**
     * @param DOMNode                $headerNode
     * @param DOMDocument            $document
     * @param Mage_Sales_Model_Order $order
     */
    protected function _addBillingInfo(
        DOMNode $headerNode,
        DOMDocument $document,
        Mage_Sales_Model_Order $order
    ) {
        /** @var Ivoinov_Wfl_Model_Export_BillingInfo $billingInfo */
        $billingInfo = Mage::getModel('ivoinov_wfl/export_billingInfo',
            array('order' => $order, 'document' => $document, 'node' => $headerNode));
        $billingInfo->process();
    }

    /**
     * @param DOMNode                $headerNode
     * @param DOMDocument            $document
     * @param Mage_Sales_Model_Order $order
     */
    protected function _addDeliveryInfo(
        DOMNode $headerNode,
        DOMDocument $document,
        Mage_Sales_Model_Order $order
    ) {
        /** @var Ivoinov_Wfl_Model_Export_DeliveryInfo $deliveryInfo */
        $deliveryInfo = Mage::getModel('ivoinov_wfl/export_deliveryInfo',
            array('order' => $order, 'document' => $document, 'node' => $headerNode));
        $deliveryInfo->process();

    }

    /**
     * @param DOMNode                     $productsNode
     * @param DOMDocument                 $document
     * @param Mage_Sales_Model_Order      $order
     * @param Mage_Sales_Model_Order_Item $orderItem
     */
    protected function _addProductInfo(
        DOMNode $productsNode,
        DOMDocument $document,
        Mage_Sales_Model_Order $order,
        Mage_Sales_Model_Order_Item $orderItem
    ) {
        /** @var Ivoinov_Wfl_Model_Export_ProductInfo $productInfo */
        $productInfo = Mage::getModel('ivoinov_wfl/export_productInfo',
            array('order' => $order, 'document' => $document, 'node' => $productsNode));
        $productInfo->setOrderItem($orderItem);
        $productInfo->process();

    }

    /**
     * @param DOMNode                $orderNode
     * @param DOMDocument            $document
     * @param Mage_Sales_Model_Order $order
     */
    protected function _addPaymentInfo(
        DOMNode $orderNode,
        DOMDocument $document,
        Mage_Sales_Model_Order $order
    ) {
        /** @var Ivoinov_Wfl_Model_Export_PaymentInfo $paymentInfo */
        $paymentInfo = Mage::getModel('ivoinov_wfl/export_paymentInfo',
            array('order' => $order, 'document' => $document, 'node' => $orderNode));
        $paymentInfo->process();

    }

    /**
     * @param DOMDocument            $document
     * @param Mage_Sales_Model_Order $order
     */
    protected function _downloadFileToFTP(DOMDocument $document, Mage_Sales_Model_Order $order)
    {
        $filename = sprintf('order_%s_%s.xml', $order->getIncrementId(),
            date('Ymd_Hi', strtotime($order->getCreatedAtStoreDate())));
        $filepath = implode(DS, array(Mage::getBaseDir('var'), 'export', 'orders'));
        if (!file_exists($filepath) || !is_writable($filepath)) {
            mkdir($filepath, 07777, true);
        }
        $document->save($filepath . DS . $filename);
        $this->_sftpHelper->sendFileToFtp($filepath . DS . $filename, self::PATH_TO_FILE_ON_FTP . $filename);
    }
}