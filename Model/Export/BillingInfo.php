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
 * Class Ivoinov_Wfl_Model_Export_BillingInfo
 */
class Ivoinov_Wfl_Model_Export_BillingInfo extends Ivoinov_Wfl_Model_Export_Abstract
{
    CONST BILLING_ADDRESS_STREET_MAX_LENGTH = 30;
    /**
     * @var array
     */
    protected $_preparedXmlData = array();

    /**
     * @var array
     */
    protected $_shippingAddressMapping
        = array(
            'b_customername' => 'customer_name',
            'b_email'        => 'email',
            'b_telephone'    => 'telephone',
            'b_mobile'       => 'telephone',
            'b_psuburb'      => 'city',
            'b_ppostcode'    => 'postcode',
            'b_pstate'       => 'region',
            'b_country'      => 'country_id',
        );

    /**
     * Process billing information.
     *
     * @return void
     */
    public function process()
    {
        if ($this->_order->getId()) {
            $this->_collectInformation();
            foreach ($this->_preparedXmlData as $xmlNodeName => $xmlNodeValue) {
                $this->_xmlNode->appendChild($this->_xmlDocument->createElement($xmlNodeName, $xmlNodeValue));
            }
        }
    }

    /**
     * Collect information for adding to XML.
     *
     * @return void
     */
    protected function _collectInformation()
    {
        /** @var Mage_Sales_Model_Order_Address $shippingAddress */
        $shippingAddress = $this->_order->getShippingAddress();
        foreach ($this->_shippingAddressMapping as $xmlNodeName => $shippingAddressFieldName) {
            $xmlNodeValue = $shippingAddress->getData($shippingAddressFieldName);
            if ($shippingAddressFieldName == 'email' && empty($xmlNodeValue)) {
                $xmlNodeValue = $this->_order->getCustomerEmail();
            }
            if (empty($xmlNodeValue)) {
                $methodName = 'get';
                $methodName .= str_replace('_', '', uc_words(str_replace('_', ' ', $shippingAddressFieldName)));
                $xmlNodeValue = $shippingAddress->$methodName();
                if (empty($xmlNodeValue)) {
                    $xmlNodeValue = $this->_order->$methodName();
                }
            }
            $this->_preparedXmlData[$xmlNodeName] = $xmlNodeValue;
        }
        $this->_processAddress();
    }

    /**
     * Separate processing for shipping address
     *
     * @return void
     */
    protected function _processAddress()
    {
        $shippingAddressStreet = $this->_order->getShippingAddress()->getStreet(-1);
        if (strlen($shippingAddressStreet) >= self::BILLING_ADDRESS_STREET_MAX_LENGTH) {
            $streetParts = explode(' ', $shippingAddressStreet);
            for ($i = 1; $i <= 4; $i++) {
                $xmlAddressField = 'b_padd' . $i;
                $xmlAddressFieldValue = '';
                foreach ($streetParts as $streetPartId => $streetPart) {
                    if (strlen($xmlAddressFieldValue . ' ' . $streetPart) <= self::BILLING_ADDRESS_STREET_MAX_LENGTH) {
                        $xmlAddressFieldValue .= ' ' . $streetPart;
                        unset($streetParts[$streetPartId]);
                    }
                }
                $this->_preparedXmlData[$xmlAddressField] = $xmlAddressFieldValue;
            }
        } else {
            $this->_preparedXmlData['b_padd1'] = $this->_order->getShippingAddress()->getStreet(-1);
        }
    }
}