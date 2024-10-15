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

/**
 * Request for Quote Product Sample Image Upload controller.
 */
class ProductImageUpload extends Action
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
     * @var \Magento\Catalog\Model\Product\Media\Config
     */
    protected $productMediaConfig;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    /**
     * @param Context $context
     * @param Filesystem $filesystem
     * @param \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory
     * @param \Magento\Catalog\Model\Product\Media\Config $productMediaConfig
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     *
     */
    public function __construct(
        Context $context,
        Filesystem $filesystem,
        \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory,
        \Magento\Catalog\Model\Product\Media\Config $productMediaConfig,
        \Magento\Framework\Json\Helper\Data $jsonHelper
    ) {
        $this->_mediaDirectory = $filesystem->getDirectoryWrite(
            DirectoryList::MEDIA
        );
        $this->_fileUploaderFactory = $fileUploaderFactory;
        $this->productMediaConfig = $productMediaConfig;
        $this->jsonHelper = $jsonHelper;
        parent::__construct($context);
    }

    public function execute()
    {
        try {
            $target = $this->_mediaDirectory->getAbsolutePath(
                $this->productMediaConfig->getBaseTmpMediaPath()
            );
            $fileUploader = $this->_fileUploaderFactory->create(
                ['fileId' => 'image']
            );
            $fileUploader->setAllowedExtensions(
                ['gif', 'jpg', 'png', 'jpeg']
            );
            $fileUploader->setFilesDispersion(true);
            $fileUploader->setAllowRenameFiles(true);
            $resultData = $fileUploader->save($target);
            $resultData['url'] = $this->productMediaConfig->getTmpMediaUrl($resultData['file']);
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
