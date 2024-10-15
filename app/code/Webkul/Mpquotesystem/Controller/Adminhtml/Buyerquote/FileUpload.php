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
use Magento\Framework\Json\Helper\Data as JsonHelper;

/**
 * Mpquotesystem Quote File Upload controller.
 */
class FileUpload extends Action
{
    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    private $_mediaDirectory;

    /**
     * @var \Magento\MediaStorage\Model\File\UploaderFactory
     */
    private $_fileUploaderFactory;

    /**
     * @var JsonHelper
     */
    private $jsonHelper;

    /**
     * @param Context                                          $context
     * @param Filesystem                                       $filesystem
     * @param \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory
     * @param JsonHelper                                       $jsonHelper
     * @param \Webkul\Mpquotesystem\Helper\Data                $helper
     */
    public function __construct(
        Context $context,
        Filesystem $filesystem,
        \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory,
        JsonHelper $jsonHelper,
        \Webkul\Mpquotesystem\Helper\Data $helper
    ) {
        $this->_mediaDirectory = $filesystem->getDirectoryWrite(
            DirectoryList::MEDIA
        );
        $this->_fileUploaderFactory = $fileUploaderFactory;
        $this->jsonHelper = $jsonHelper;
        $this->helper = $helper;
        parent::__construct($context);
    }

    /**
     * File upload action
     */
    public function execute()
    {
        try {
            $errors = $this->helper->validateFiles($this->getRequest()->getFiles());
            if (empty($errors)) {
                $targets = $this->_mediaDirectory->getAbsolutePath('wkquote\files');
                $fileUploader = $this->_fileUploaderFactory->create(
                    ['fileId' => 'files']
                );
                $allowedType = $this->helper->getAllowedTypes();
                $allowedExtensions = [];
                if ($allowedType) {
                    $allowedExtensions = explode(',', $allowedType);
                }
                if (empty($allowedExtensions)) {
                    $allowedExtensions = ['gif', 'jpg', 'png', 'jpeg', 'pdf', 'doc', 'zip'];
                }
                $fileUploader->validateFile();
                $fileUploader->setAllowedExtensions(
                    $allowedExtensions
                );
                $fileUploader->setFilesDispersion(true);
                $fileUploader->setAllowRenameFiles(true);
                $resultData = $fileUploader->save($targets);
                unset($resultData['tmp_name']);
                unset($resultData['path']);
                $resultData['extension'] = $resultData['file'];
                $this->getResponse()->representJson(
                    $this->jsonHelper->jsonEncode($resultData)
                );
            } else {
                foreach ($errors as $key => $errorMessage) {
                    $this->messageManager->addError($errorMessage);
                    $this->getResponse()->representJson(
                        $this->jsonHelper->jsonEncode(
                            [
                            'error' => $errorMessage,
                            'errorcode' => 1
                            ]
                        )
                    );
                }
            }
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
