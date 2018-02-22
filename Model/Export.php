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
 * Class Ivoinov_Wfl_Model_Export
 */
abstract class Ivoinov_Wfl_Model_Export
{
    /**
     * @var Ivoinov_Wfl_Helper_Sftp|Mage_Core_Helper_Abstract
     */
    protected $_sftpHelper;

    /**
     * Ivoinov_Wfl_Model_Export_Order constructor.
     */
    public function __construct()
    {
        $this->_sftpHelper = Mage::helper('ivoinov_wfl/sftp');
    }

    /**
     * Main entry point for export models.
     *
     * @return void
     */
    abstract public function export();
}