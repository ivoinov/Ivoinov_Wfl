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

class Ivoinov_Wfl_Model_Sales_Quote_Address extends Mage_Sales_Model_Quote_Address
{
    /**
     * @param Mage_Sales_Model_Quote_Item_Abstract|null $item
     *
     * @return bool
     */
    public function requestShippingRates(Mage_Sales_Model_Quote_Item_Abstract $item = null)
    {
        $found = parent::requestShippingRates($item);
        if ($item->getQuote()->getStore()->getCode() == 'ic') {
            /** @var Mage_Sales_Model_Quote_Address_Rate $shippingRate */
            $lowerShippingRate = false;
            foreach ($this->getShippingRatesCollection() as $shippingRate) {
                if ($lowerShippingRate === false || $shippingRate->getPrice() <= $lowerShippingRate->getPrice()) {
                    $lowerShippingRate = $shippingRate;
                }
            }
            $shippingDescription = $lowerShippingRate->getCarrierTitle() . ' - ' . $lowerShippingRate->getMethodTitle();
            $this->setShippingAmount(0)->setBaseShippingAmount(0)->setShippingMethod($lowerShippingRate->getCode())
                ->setShippingDescription($shippingDescription);

            $found = true;
        }

        return $found;
    }
}