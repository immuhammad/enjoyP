<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_B2BMarketplace
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\B2BMarketplace\Controller\Supplier\Profile;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Json\Helper\Data as JsonHelper;

/**
 * B2BMarketplace Supplier Profile Message File Upload controller.
 */
class FileUpload extends Action
{
    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    protected $_mediaDirectory;

    /**
     * File Uploader factory.
     *
     * @var \Magento\MediaStorage\Model\File\UploaderFactory
     */
    protected $_fileUploaderFactory;

    /**
     * JsonHelper.
     *
     * @var JsonHelper
     */
    protected $jsonHelper;

    /**
     * @param Context $context
     * @param JsonHelper $jsonHelper
     *
     */
    public function __construct(
        Context $context,
        Filesystem $filesystem,
        \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory,
        JsonHelper $jsonHelper
    ) {
        $this->_mediaDirectory = $filesystem->getDirectoryWrite(
            DirectoryList::MEDIA
        );
        $this->_fileUploaderFactory = $fileUploaderFactory;
        $this->jsonHelper = $jsonHelper;
        parent::__construct($context);
    }

    public function execute()
    {
        try {
            $target = $this->_mediaDirectory->getAbsolutePath(
                \Webkul\B2BMarketplace\Model\QuoteManagement::ATTACHMENTS_FOLDER
            );
            $fileUploader = $this->_fileUploaderFactory->create(
                ['fileId' => 'files']
            );
            $fileUploader->setAllowedExtensions(
                ['gif', 'jpg', 'png', 'jpeg', 'pdf', 'doc', 'zip']
            );
            $fileUploader->setFilesDispersion(true);
            $fileUploader->setAllowRenameFiles(true);
            $resultData = $fileUploader->save($target);
            unset($resultData['tmp_name']);
            unset($resultData['path']);
            $resultData['extension'] = $resultData['file'];
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
