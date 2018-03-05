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
 * Class Ivoinov_Wfl_Model_Import_Order
 *
 * @property Ivoinov_Wfl_Helper_Data $_helper
 * @property Ivoinov_Wfl_Helper_Ftp  $_ftpHelper
 */
class Ivoinov_Wfl_Model_Import_Order extends Ivoinov_Wfl_Model_Import
{
    CONST PATH_TO_ORDER_FILES                    = 'import/orders';
    CONST PATH_TO_FILES_ON_FTP                   = '/Status_Update/New';
    CONST ORDER_FILES_MASK                       = 'OSL_*.xml';
    CONST TRACK_CARRIER_CODE                     = 'custom';
    CONST DELIVERY_ORDER_STATUS_PICKED_UP_FULL   = '1302';
    CONST DELIVERY_ORDER_STATUS_PICKED_PARTIALLY = '1301';

    protected $_ordersXPATH = 'ConfirmationBody/Orders';
    protected $_orderIncrementIdXPATH = 'Order/OrderNumber';
    protected $_iconicOrderIncrementIdXPATH = 'Order/CustomerOrder';
    protected $_orderStatusXPATH = 'Order/Status';
    protected $_orderTrackingNumberXPATH = 'Order/ConNote';
    protected $_orderItemsXPATH = 'Order/items';
    protected $_orderItemProductSkuXPATH = 'item/ProductCode';
    protected $_orderItemProductBarcodeXPATH = 'item/BarCode';

    /**
     * {@inheritdoc}
     */
    public function import()
    {
        try {
            $orderFiles = $this->_loadOrderFiles();
            foreach ($orderFiles as $file) {
                try {
                    /** @var SimpleXMLElement $orderXml */
                    $orderXml = simplexml_load_file($file);
                    if ($orderXml === false) {
                        throw new Exception($this->_helper->__('%s file couldn\'t be loaded. Check file owner or permissions'));
                    }
                    $orders = $orderXml->xpath($this->_ordersXPATH);
                    if ($orders === false) {
                        throw new Exception($this->_helper->__('%s order node is missing', $this->_ordersXPATH));
                    }
                    foreach ($orders as $order) {
                        $incrementId = $order->xpath($this->_orderIncrementIdXPATH);
                        if ($incrementId === false || !count($incrementId) || !is_array($incrementId)) {
                            $incrementId = $order->xpath($this->_iconicOrderIncrementIdXPATH);
                            if ($incrementId === false || !count($incrementId) || !is_array($incrementId)) {
                                throw new Exception($this->_helper->__('%s increment id node is missing'));
                            }
                        }
                        $orderModel = Mage::getModel('sales/order')->loadByIncrementId((string)$incrementId[0]);
                        if (!$orderModel || !$orderModel->getId()) {
                            throw new Exception($this->_helper->__('Order was not found in magento. Order increment id - %s',
                                $incrementId));
                        }
                        $statuses = $order->xpath($this->_orderStatusXPATH);
                        if ($statuses === false || !count($statuses) || !is_array($statuses)) {
                            // Continue as status node is missing
                            $this->_moveFileToArchive($file);
                            continue;
                        }
                        if ((string)$statuses[0] == self::DELIVERY_ORDER_STATUS_PICKED_PARTIALLY
                            || (string)$statuses[0] == self::DELIVERY_ORDER_STATUS_PICKED_UP_FULL) {
                            $this->_createShipping($orderModel, $order);
                            $this->_setOrderToComplete($orderModel);
                            $this->_moveFileToArchive($file);
                        } else {
                            // Continue as status node is missing
                            $this->_moveFileToArchive($file);
                            continue;
                        }
                    }
                } catch (Exception $e) {
                    Mage::logException($e);
                    echo $file . "\n";
                    echo $e->getMessage() . "\n";
                    continue;
                }
            }
        } catch (Exception $e) {
            Mage::logException($e);
        }
    }

    /**
     * @return array
     * @throws Exception
     */
    protected function _loadOrderFiles()
    {
        $filepath = rtrim(Mage::getBaseDir('var'), DS) . DS . self::PATH_TO_ORDER_FILES;
        if (!file_exists($filepath)) {
            throw new Exception($this->_helper->__('%s filepath doesn\'t exist or not readable', $filepath));
        }
        $files = $this->_sftpHelper->loadFilesFromFtp(self::PATH_TO_FILES_ON_FTP, $filepath);
        if (empty($files)) {
            return glob(rtrim($filepath, DS) . DS . '*.*');
        }

        return $files;
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @param SimpleXMLElement       $xmlOrderNode
     *
     * @throws Exception
     */
    protected function _createShipping(Mage_Sales_Model_Order $order, SimpleXMLElement $xmlOrderNode)
    {
        if (!$order->canShip()) {
            throw new Exception($this->_helper->__('Order %s can\'t be shipped', $order->getIncrementId()));
        }
        /** @var Mage_Sales_Model_Service_Order $serviceModel */
        $serviceModel = Mage::getModel('sales/service_order', $order);
        $itemsQty = array();
        $itemsNode = $xmlOrderNode->xpath($this->_orderItemsXPATH);
        if ($itemsNode === false) {
            throw new Exception($this->_helper->__('Items node is missing in order node'));
        }
        if (!array($itemsNode) || !count($itemsNode)) {
            throw new Exception($this->_helper->__('Items node is empty or missing'));
        }
        foreach ($itemsNode as $itemXmlNode) {
            /** @var Mage_Sales_Model_Order_Item $orderItem */
            $orderItem = $this->_getOrderItem($order, $itemXmlNode);
            if ($orderItem->getParentItemId()) {
                $itemsQty[$orderItem->getParentItem()->getId()] = $orderItem->getParentItem()->getQtyToShip();
            } else {
                $itemsQty[$orderItem->getId()] = $orderItem->getQtyToShip();
            }
        }
        if ($itemsQty === array()) {
            throw new Exception($this->_helper->__('No items to ship. Please, check xml file. Order increment id - %s',
                $order->getIncrementId()));
        }
        /** @var Mage_Sales_Model_Order_Shipment $shipment */
        $shipment = $serviceModel->prepareShipment($itemsQty);
        $shipment->register();
        $trackingNumberArray = $xmlOrderNode->xpath($this->_orderTrackingNumberXPATH);
        if ($trackingNumberArray !== false && count($trackingNumberArray)) {
            $track = $this->_createTrack((string)$trackingNumberArray[0], $order);
            $shipment->addTrack($track);
        }
        $shipment->save();
        if (isset($track)) {
            $track->save();
        }
        $shipment->sendEmail(true);
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @param SimpleXMLElement       $itemNode
     *
     * @return Mage_Sales_Model_Order_Item
     * @throws Exception
     */
    protected function _getOrderItem(Mage_Sales_Model_Order $order, SimpleXMLElement $itemNode)
    {
        $productSku = $this->_getProductSku($itemNode);
        $productBarcode = $this->_getProductBarcode($itemNode);
        /** @var Mage_Sales_Model_Order_Item $orderItem */
        foreach ($order->getAllItems() as $orderItem) {
            /** @var Mage_Catalog_Model_Product $product */
            $product = $orderItem->getProduct();
            if ($product->getSku() == $productSku) {
                return $orderItem;
            }
            $productBarcodeValue = $product->getResource()
                ->getAttributeRawValue($product->getId(), 'barcode', $order->getStoreId());
            if ($productBarcodeValue == $productBarcode) {
                return $orderItem;
            }
        }
        throw new Exception($this->_helper->__('Can\'t find order item in order %s. SKU - %s , barcode - %s',
            $order->getIncrementId(), $productSku, $productBarcode));
    }

    /**
     * @param SimpleXMLElement $itemNode
     *
     * @return string
     * @throws Exception
     */
    protected function _getProductSku(SimpleXMLElement $itemNode)
    {
        $sku = $itemNode->xpath($this->_orderItemProductSkuXPATH);
        if ($sku === false) {
            throw new Exception($this->_helper->__('Can not find %s node in <item>', $this->_orderItemProductSkuXPATH));
        }
        if (!is_array($sku) || !count($sku)) {
            throw new Exception($this->_helper->__('%s node is empty or doesn\'t exist',
                $this->_orderItemProductSkuXPATH));
        }

        return (string)$sku[0];
    }

    /**
     * @param SimpleXMLElement $itemNode
     *
     * @return string
     * @throws Exception
     */
    protected function _getProductBarcode(SimpleXMLElement $itemNode)
    {
        $barcode = $itemNode->xpath($this->_orderItemProductBarcodeXPATH);
        if ($barcode === false) {
            throw new Exception($this->_helper->__('Can not find %s node in <item>',
                $this->_orderItemProductBarcodeXPATH));
        }
        if (!is_array($barcode) || !count($barcode)) {
            throw new Exception($this->_helper->__('%s node is empty or doesn\'t exist',
                $this->_orderItemProductBarcodeXPATH));
        }

        return (string)$barcode[0];
    }

    /**
     * @param Mage_Sales_Model_Order $order
     *
     * @throws Exception
     */
    protected function _setOrderToComplete(Mage_Sales_Model_Order $order)
    {
        $order->setData('state', Mage_Sales_Model_Order::STATE_COMPLETE);
        $order->setData('status', Mage_Sales_Model_Order::STATE_COMPLETE);
        $order->save();
    }

    /**
     * @param string                 $trackingNumber
     * @param Mage_Sales_Model_Order $order
     *
     * @return Mage_Sales_Model_Order_Shipment_Track
     */
    protected function _createTrack($trackingNumber, Mage_Sales_Model_Order $order)
    {
        /** @var Mage_Sales_Model_Order_Shipment_Track $track */
        $track = Mage::getModel('sales/order_shipment_track');
        $track->setNumber($trackingNumber);
        $track->setCarrierCode(self::TRACK_CARRIER_CODE);
        $track->setTitle($this->_getShippingTitle($order));

        return $track;
    }

    /**
     * Return magento DB connection.
     *
     * @return Varien_Db_Adapter_Interface
     */
    protected function _getDBConnection()
    {
        return Mage::getSingleton('core/resource')->getConnection('core_write');
    }

    protected function _moveFileToArchive($filePath)
    {
        $fileName = basename($filePath);
        $newFilePath = implode(DS, array(
            rtrim(str_replace($fileName, '', $filePath), DS),
            'archive',
            $fileName,
        ));
        mkdir(dirname($newFilePath), 0777, true);
        rename($filePath, $newFilePath);
    }

    /**
     * Return shipping method title depends on country
     *
     *
     * @param Mage_Sales_Model_Order $order
     *
     * @return string
     */
    protected function _getShippingTitle(Mage_Sales_Model_Order $order)
    {
        $shippingAddress = $order->getShippingAddress();
        if ($shippingAddress->getCountryId() == 'au') {
            return 'Aus Post';
        }
        if ($shippingAddress->getCountryId() == 'nz') {
            return 'DHL Ecommerce';
        }

        return $order->getShippingDescription();
    }
}