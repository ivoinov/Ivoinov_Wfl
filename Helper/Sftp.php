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
 * Class Ivoinov_Wfl_Helper_Sftp
 */
class Ivoinov_Wfl_Helper_Sftp extends Ivoinov_Wfl_Helper_Ftp
{
    /**
     * @var Net_SFTP
     */
    protected $_client;

    /**
     * Ivoinov_Wfl_Helper_Sftp constructor.
     */
    public function __construct()
    {
        $this->_client = new Varien_Io_Sftp();
        $this->_client->open(array(
            'host'     => $this->getHost(),
            'port'     => $this->getPort(),
            'timeout'  => Ivoinov_Wfl_Helper_Ftp::DEFAULT_FTP_TIMEOUT,
            'username' => $this->getUserName(),
            'password' => $this->getPassword(),
        ));

        return $this;
    }

    public function sendFileToFtp($localFilepath, $remoteFilepath)
    {
        $this->_client->write($remoteFilepath, $localFilepath);
    }

    public function loadFilesFromFtp($remoteDirPath, $localFolder)
    {
        $this->_client->cd($remoteDirPath);
        $files = $this->_client->ls();
        if (empty($files)) {
            return array();
        }
        foreach ($files as $remoteFilePath) {
            if (!isset($remoteFilePath['text']) || $remoteFilePath['text'] == '.' || $remoteFilePath['text'] == '..') {
                continue;
            }
            $this->_client->read($remoteFilePath['text'], rtrim($localFolder, DS) . DS . $remoteFilePath['text']);

            return glob(rtrim($localFolder, DS) . DS . '*.*');
        }
    }
}