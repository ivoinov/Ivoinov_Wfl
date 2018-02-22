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
 * Class Ivoinov_Wfl_Model_Export_ProductInfo
 */
class Ivoinov_Wfl_Model_Export_ProductInfo extends Ivoinov_Wfl_Model_Export_Abstract
{
    CONST WEIGHT_DEFAULT_VALUE = 1;
    /**
     * @var Mage_Sales_Model_Order_Item
     */
    protected $_orderItem;
    /**
     * @var array
     */
    protected $_sizeAttributeCodes
        = array(
            'size',
            'shoe_size',
            'luggage_size',
            'accessories_size',
            'cpsizechart',
        );
    /**
     * @var array
     */
    protected $_styleAttributeCodes
        = array(
            'style',
            'frame_style',
            'homeware_style',
            'luggage_travel_style',
            'luggage_style',
        );
    /**
     * @var array
     */
    protected $_colorAttributeCodes
        = array(
            'color',
        );

    /**
     *
     */
    public function process()
    {
        $product = $this->_xmlNode->appendChild($this->_xmlDocument->createElement('product'));
        /** @var Mage_Catalog_Model_Product $productModel */
        $productModel = Mage::getModel('catalog/product')->load($this->_orderItem->getProductId());
        $parentItem = $this->_orderItem;
        if ($this->_orderItem->hasParentItemId()) {
            $parentItem = $this->_order->getItemById($this->_orderItem->getParentItemId());
        }
        $product->appendChild($this->_xmlDocument->createElement('orderLineId', $this->_orderItem->getId()));
        $product->appendChild($this->_xmlDocument->createElement('productid', $productModel->getData('barcode')));
        $product->appendChild($this->_xmlDocument->createElement('barcode', $productModel->getData('barcode')));
        $product->appendChild($this->_xmlDocument->createElement('description', $productModel->getName()));
        $product->appendChild($this->_xmlDocument->createElement('skusize', $this->_getSize($productModel)));
        $product->appendChild($this->_xmlDocument->createElement('stylecode', $productModel->getSku()));
        $product->appendChild($this->_xmlDocument->createElement('colour', $this->_getColor($productModel)));
        $product->appendChild($this->_xmlDocument->createElement('quantityordered', (int)$parentItem->getQtyOrdered()));
        $product->appendChild($this->_xmlDocument->createElement('quantityBackorder',
            (int)$parentItem->getQtyBackordered()));
        $product->appendChild($this->_xmlDocument->createElement('quantitytodeliver',
            (int)$parentItem->getQtyToShip()));
        $product->appendChild($this->_xmlDocument->createElement('taxType', 'GST'));
        $product->appendChild($this->_xmlDocument->createElement('taxAmount',
            abs($this->_helper->getDeliveryTaxAmount($parentItem->getPrice()))));
        $product->appendChild($this->_xmlDocument->createElement('currency', $this->_order->getOrderCurrencyCode()));
        $product->appendChild($this->_xmlDocument->createElement('discount',
            abs(number_format($parentItem->getDiscountAmount(), 2))));
        $product->appendChild($this->_xmlDocument->createElement('discountdescription'));
        $product->appendChild($this->_xmlDocument->createElement('retailprice',
            abs(number_format($parentItem->getPrice(), 2))));
        $product->appendChild($this->_xmlDocument->createElement('fabricContent', 'n/a'));
        $product->appendChild($this->_xmlDocument->createElement('weight', abs($this->_getWeight($productModel))));
        $product->appendChild($this->_xmlDocument->createElement('isGiftWrapped', false));
        $product->appendChild($this->_xmlDocument->createElement('giftMessage', false));
        $product->appendChild($this->_xmlDocument->createElement('giftwrapType', false));
        $product->appendChild($this->_xmlDocument->createElement('specialInstructions', false));
        $product->appendChild($this->_xmlDocument->createElement('giftVoucherNo', false));
        $product->appendChild($this->_xmlDocument->createElement('giftVoucherPin', false));
        $product->appendChild($this->_xmlDocument->createElement('giftVoucherExpiryDate', false));
    }

    /**
     * @param Mage_Sales_Model_Order_Item $orderItem
     */
    public function setOrderItem(Mage_Sales_Model_Order_Item $orderItem)
    {
        $this->_orderItem = $orderItem;
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     *
     * @return string
     */
    protected function _getWeight(Mage_Catalog_Model_Product $product)
    {
        $productWeight = $product->getWeight();
        if ($productWeight) {
            return number_format($productWeight, 4);
        }
        $attributeSetName = Mage::getModel('eav/entity_attribute_set')->load($product->getAttributeSetId())
            ->getAttributeSetName();
        if ($attributeSetName == 'Shoes') {
            $productWeight = 1.2;
        } elseif ($attributeSetName == 'ACCESSORIES' || $attributeSetName == 'Eyewear'
            || $attributeSetName == 'Jewelry') {
            $productWeight = 0.4;
        } else {
            $productWeight = self::WEIGHT_DEFAULT_VALUE;
        }

        return number_format($productWeight, 4);
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     *
     * @return null|string
     */
    protected function _getSize(Mage_Catalog_Model_Product $product)
    {
        foreach ($this->_sizeAttributeCodes as $sizeAttributeCode) {
            $attributeValue = $product->getAttributeText($sizeAttributeCode);
            if (!empty($attributeValue)) {
                return $attributeValue;
            }
        }

        return null;
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     *
     * @return null|string
     */
    protected function _getStyle(Mage_Catalog_Model_Product $product)
    {
        foreach ($this->_styleAttributeCodes as $styleAttributeCode) {
            $attributeValue = $product->getAttributeText($styleAttributeCode);
            if (!empty($attributeValue)) {
                return $attributeValue;
            }
        }

        return null;

    }

    /**
     * @param Mage_Catalog_Model_Product $product
     *
     * @return null|string
     */
    protected function _getColor(Mage_Catalog_Model_Product $product)
    {
        foreach ($this->_colorAttributeCodes as $colorAttributeCode) {
            $attributeValue = $product->getAttributeText($colorAttributeCode);
            if (!empty($attributeValue)) {
                return $attributeValue;
            }
        }

        return null;
    }
}