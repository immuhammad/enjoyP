<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Mpquotesystem
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Mpquotesystem\Controller\Adminhtml\Buyerquote;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Driver\File as DriverFile;
use Magento\Framework\Json\Helper\Data as JsonHelper;

/**
 * Mpquotesystem Quote File Delete controller.
 */
class FileDelete extends Action
{
    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    private $_mediaDirectory;

    /**
     * @var DriverFile
     */
    private $driverFiles;

    /**
     * @var JsonHelper
     */
    private $jsonHelper;

    /**
     * @param Context    $context
     * @param Filesystem $filesystem
     * @param DriverFile $driverFile
     * @param JsonHelper $jsonHelper
     */
    public function __construct(
        Context $context,
        Filesystem $filesystem,
        DriverFile $driverFile,
        JsonHelper $jsonHelper
    ) {
        $this->_mediaDirectory = $filesystem->getDirectoryWrite(
            DirectoryList::MEDIA
        );
        $this->driverFiles = $driverFile;
        $this->jsonHelper = $jsonHelper;
        parent::__construct($context);
    }

    /**
     * File delete action
     */
    public function execute()
    {
        try {
            $fileName = $this->getRequest()->getParam('file_name');
            $targetPath = $this->_mediaDirectory->getAbsolutePath('wkquote\files');
            $resultData['error'] = 1;
            if ($this->driverFiles->isExists($targetPath.$fileName)) {
                $this->driverFiles->deleteFile($targetPath.$fileName);
                $resultData['error'] = 0;
            }
            $this->getResponse()->representJson(
                $this->jsonHelper->jsonEncode($resultData)
            );
        } catch (\Exception $e) {
            $this->getResponse()->representJson(
                $this->jsonHelper->jsonEncode(
                    [
                        'error' => $e->getMessage(),
                        'errorcode' => $e->getCode(),
                    ]
                )
            );
        }
    }
}
