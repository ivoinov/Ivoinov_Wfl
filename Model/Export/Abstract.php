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
 * Class Ivoinov_Wfl_Model_Export_Abstract
 */
abstract class Ivoinov_Wfl_Model_Export_Abstract
{
    /**
     * @var Mage_Sales_Model_Order
     */
    protected $_order;
    /**
     * @var DOMDocument
     */
    protected $_xmlDocument;
    /**
     * @var DOMNode
     */
    protected $_xmlNode;
    /**
     * @var Ivoinov_Wfl_Helper_Data
     */
    protected $_helper;

    /**
     * Ivoinov_Wfl_Model_Export_Abstract constructor.
     *
     * @param array $data
     *
     * @throws Mage_Core_Exception
     */
    public function __construct(array $data = array())
    {
        $this->_order = isset($data['order']) ? $data['order'] : Mage::getModel('sales/order');
        $this->_xmlDocument = isset($data['document']) ? $data['document'] : new DOMDocument('1.0', 'UTF-8');
        if (!$data['node']) {
            Mage::throwException('DOMNode is required parameter');
        }
        $this->_xmlNode = $data['node'];
        $this->_helper = Mage::helper('ivoinov_wfl');
    }

    /**
     * Main entry point for export operations.
     *
     * @return void
     */
    abstract function process();
}