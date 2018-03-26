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
 * Class Ivoinov_Wfl_Model_Export_DeliveryInfo
 */
class Ivoinov_Wfl_Model_Export_DeliveryInfo extends Ivoinov_Wfl_Model_Export_Abstract
{
    CONST DELIVERY_BRANCH_NUMBER     = '0001';
    CONST DELIVERY_PRIORITY          = 'standard';
    CONST DELIVERY_COMPANY_ID        = 'ASN0';
    CONST ICONIC_DELIVERY_COMPANY_ID = 'ASI0';
    CONST DELIVERY_DIVISION          = 'AS';
    CONST DELIVERY_CHANEL            = 'Online';
    CONST DELIVERY_TAX_TYPE          = 'GST';

    /**
     *
     */
    public function process()
    {
        $this->_xmlNode->appendChild($this->_xmlDocument->createElement('deliveryid'));
        $this->_xmlNode->appendChild($this->_xmlDocument->createElement('invoiceinventorydetailid'));
        $this->_xmlNode->appendChild($this->_xmlDocument->createElement('orderdate',
            date('d/m/Y H:i:s', strtotime($this->_order->getCreatedAtStoreDate()))));
        $this->_xmlNode->appendChild($this->_xmlDocument->createElement('delivDateServiceCode'));
        $this->_xmlNode->appendChild($this->_xmlDocument->createElement('delivDateServiceAttributeName'));
        $this->_xmlNode->appendChild($this->_xmlDocument->createElement('delivdate'));
        $this->_xmlNode->appendChild($this->_xmlDocument->createElement('delivTimeServiceCode'));
        $this->_xmlNode->appendChild($this->_xmlDocument->createElement('delivTimeServiceAttributeName'));
        $this->_xmlNode->appendChild($this->_xmlDocument->createElement('delivTimeStartTime'));
        $this->_xmlNode->appendChild($this->_xmlDocument->createElement('delivTimeEndTime'));
        $this->_xmlNode->appendChild($this->_xmlDocument->createElement('branchNumber', self::DELIVERY_BRANCH_NUMBER));
        $this->_xmlNode->appendChild($this->_xmlDocument->createElement('customerid', $this->_order->getCustomerId()));
        $this->_xmlNode->appendChild($this->_xmlDocument->createElement('ordertype'));
        $this->_xmlNode->appendChild($this->_xmlDocument->createElement('freightcharge',
            abs(number_format($this->_order->getShippingAmount(), 2))));
        $this->_xmlNode->appendChild($this->_xmlDocument->createElement('shipCarrier',
            $this->_getShippingCarrierCode()));
        $this->_xmlNode->appendChild($this->_xmlDocument->createElement('deliveryPriority', self::DELIVERY_PRIORITY));
        $this->_xmlNode->appendChild($this->_xmlDocument->createElement('status', $this->_order->getStatus()));
        $this->_xmlNode->appendChild($this->_xmlDocument->createElement('delivinstruct'));
        $this->_xmlNode->appendChild($this->_xmlDocument->createElement('companyid', $this->_getDeliveryCompany()));
        $this->_xmlNode->appendChild($this->_xmlDocument->createElement('division', self::DELIVERY_DIVISION));
        $this->_xmlNode->appendChild($this->_xmlDocument->createElement('channel', self::DELIVERY_CHANEL));
        $this->_xmlNode->appendChild($this->_xmlDocument->createElement('subtotal',
            abs(number_format($this->_order->getSubtotal(), 2))));
        $this->_xmlNode->appendChild($this->_xmlDocument->createElement('discount',
            abs(number_format($this->_order->getDiscountAmount(), 2))));
        $this->_xmlNode->appendChild($this->_xmlDocument->createElement('grandTotal',
            abs(number_format($this->_order->getGrandTotal(), 2))));
        $this->_xmlNode->appendChild($this->_xmlDocument->createElement('taxAmount',
            $this->_helper->getDeliveryTaxAmount($this->_order->getSubtotal())));
        $this->_xmlNode->appendChild($this->_xmlDocument->createElement('taxType', self::DELIVERY_TAX_TYPE));
        $this->_xmlNode->appendChild($this->_xmlDocument->createElement('taxRate',
            $this->_helper->getDeliveryTaxPercent()));
        $this->_xmlNode->appendChild($this->_xmlDocument->createElement('currency',
            $this->_order->getBaseCurrencyCode()));
    }

    /**
     * @return string
     */
    protected function _getShippingCarrierCode()
    {
        if ($this->_order->getStore()->getCode() == 'ic') {
            return 'APSEXP';
        } elseif ($this->_order->getShippingAddress()->getCountryId() == 'nz'
            || $this->_order->getShippingAddress()->getCountryId() == 'au') {
            return 'APSSTD';
        } elseif ($this->_order->getShippingMethod(true)->getMethod() == 'shipping_express') {
            return 'APSEXP';
        } else {
            return 'DHLGML';
        }
    }

    /**
     * @return string
     */
    protected function _getDeliveryCompany()
    {
        if ($this->_order->getStore()->getCode() == 'ic') {
            return self::ICONIC_DELIVERY_COMPANY_ID;
        }

        return self::DELIVERY_COMPANY_ID;
    }
}