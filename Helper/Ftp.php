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
 * Class Ivoinov_Wfl_Helper_Ftp
 */
class Ivoinov_Wfl_Helper_Ftp extends Mage_Core_Helper_Abstract
{
    CONST XML_CONFIG_PATH_FTP_IS_ENABLED = 'ivoinov_wfl/ftp/enable';
    CONST XML_CONFIG_PATH_FTP_HOST       = 'ivoinov_wfl/ftp/host';
    CONST XML_CONFIG_PATH_FTP_PORT       = 'ivoinov_wfl/ftp/port';
    CONST XML_CONFIG_PATH_FTP_USERNAME   = 'ivoinov_wfl/ftp/username';
    CONST XML_CONFIG_PATH_FTP_PASSWORD   = 'ivoinov_wfl/ftp/password';
    CONST DEFAULT_FTP_PORT               = 21;
    CONST DEFAULT_FTP_TIMEOUT            = 90;

    /**
     * Download file to FTP. Return true if success, false in case failed.
     *
     * @param string $localFilepath
     * @param static $remoteFilepath
     *
     * @return boolean $result
     */
    public function sendFileToFtp($localFilepath, $remoteFilepath)
    {

        try {
            $connectionId = ftp_connect($this->getHost(), $this->getPort(), self::DEFAULT_FTP_TIMEOUT);
            if (!ftp_login($connectionId, $this->getUserName(), $this->getPassword())) {
                throw new Exception($this->__('Can\'t connect to FTP server with. User name and password incorrect'));
            }
            if (!file_exists($localFilepath)) {
                throw new Exception($this->__('Local file doesn\'t exist. File path - %s', $localFilepath));
            }
            if (!ftp_chdir($connectionId, dirname($remoteFilepath))) {
                throw new Exception($this->__('Can\'t change directory on remote server. Directory %s doesn\'t exist or not readable',
                    dirname($remoteFilepath)));
            }
            if (!ftp_put($connectionId, $remoteFilepath, $localFilepath, FTP_ASCII)) {
                throw new Exception($this->__('Error during load file to FTP server'));
            }
            ftp_close($connectionId);

            return true;

        } catch (Exception $e) {
            Mage::logException($e);

            return false;
        }


    }

    /**
     * Load files from ftp.
     *
     * @param string $remoteDirPath
     * @param string $localFolder
     *
     * @return bool
     */
    public function loadFilesFromFtp($remoteDirPath, $localFolder)
    {
        try {
            $connectionId = ftp_connect($this->getHost(), $this->getPort(), self::DEFAULT_FTP_TIMEOUT);
            if (!ftp_login($connectionId, $this->getUserName(), $this->getPassword())) {
                throw new Exception($this->__('Can\'t connect to FTP server with. User name and password incorrect'));
            }
            if (!ftp_chdir($connectionId, dirname($remoteDirPath))) {
                throw new Exception($this->__('Can\'t change directory on remote server. Directory %s doesn\'t exist or not readable',
                    dirname($remoteDirPath)));
            }

            ftp_close($connectionId);

            return true;
        } catch (Exception $e) {
            Mage::logException($e);

            return false;
        }
    }

    /**
     * Return flag is FTP configuration enabled.
     *
     * @return bool
     */
    public function isEnabled()
    {
        return Mage::getStoreConfigFlag(self::XML_CONFIG_PATH_FTP_IS_ENABLED);
    }

    /**
     * Return FTP server host.
     *
     * @return string
     */
    public function getHost()
    {
        return Mage::getStoreConfig(self::XML_CONFIG_PATH_FTP_HOST);
    }

    /**
     * Return FTP server username.
     *
     * @return string
     */
    public function getUserName()
    {
        return Mage::getStoreConfig(self::XML_CONFIG_PATH_FTP_USERNAME);
    }

    /**
     * Return FTP server password.
     *
     * @return string
     */
    public function getPassword()
    {
        return Mage::getStoreConfig(self::XML_CONFIG_PATH_FTP_PASSWORD);
    }

    /**
     * Return FTP server port.
     *
     * @return int
     */
    public function getPort()
    {
        return Mage::getStoreConfig(self::XML_CONFIG_PATH_FTP_PORT)
            ? (int)Mage::getStoreConfig(self::XML_CONFIG_PATH_FTP_PORT) : self::DEFAULT_FTP_PORT;
    }
}