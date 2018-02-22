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
 * Class Ivoinov_Wfl_Model_Export_ShippingInfo
 */
class Ivoinov_Wfl_Model_Export_ShippingInfo extends Ivoinov_Wfl_Model_Export_Abstract
{
    /**
     * @var array
     */
    protected $_shippingAddressMapping
        = array(
            'd_customername' => 'customer_name',
            'd_email'        => 'email',
            'd_telephone'    => 'telephone',
            'd_mobile'       => 'telephone',
            'd_padd1'        => 'street1',
            'd_padd2'        => 'street2',
            'd_padd3'        => 'street3',
            'd_padd4'        => 'street4',
            'd_psuburb'      => 'city',
            'd_ppostcode'    => 'postcode',
            'd_pstate'       => 'region',
            'd_country'      => 'country_id',
        );

    /**
     * Process shipping information.
     *
     * return void
     */
    public function process()
    {
        if ($this->_order->getId()) {
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
                $this->_xmlNode->appendChild($this->_xmlDocument->createElement($xmlNodeName, $xmlNodeValue));
            }
        }
    }
}