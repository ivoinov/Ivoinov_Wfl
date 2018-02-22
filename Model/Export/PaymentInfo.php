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
 * Class Ivoinov_Wfl_Model_Export_PaymentInfo
 */
class Ivoinov_Wfl_Model_Export_PaymentInfo extends Ivoinov_Wfl_Model_Export_Abstract
{
    /**
     * {@inheritdoc}
     */
    public function process()
    {
        $paymentMethodsNode = $this->_xmlNode->appendChild($this->_xmlDocument->createElement('paymentMethods'));
        /** @var Mage_Sales_Model_Order_Payment $payment */
        foreach ($this->_order->getPaymentsCollection() as $payment) {
            $paymentMethodNode = $paymentMethodsNode->appendChild($this->_xmlDocument->createElement('paymentMethod'));
            $paymentMethodNode->appendChild($this->_xmlDocument->createElement('CCName', $payment->getCcOwner()));
            $paymentMethodNode->appendChild($this->_xmlDocument->createElement('CCAmount',
                abs(number_format($payment->getAmountPaid(), 2))));
            $paymentMethodNode->appendChild($this->_xmlDocument->createElement('CCNumber', $payment->getCcNumberEnc()));
            $paymentMethodNode->appendChild($this->_xmlDocument->createElement('CCExpiryYear',
                $payment->getCcExpYear()));
            $paymentMethodNode->appendChild($this->_xmlDocument->createElement('CCType', $payment->getMethod()));
            $paymentMethodNode->appendChild($this->_xmlDocument->createElement('CCAuthenticationCode'));
        }
    }
}