<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpStripe
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpStripe\Model;

class ImageUploader
{
    /**
     * @var \Magento\MediaStorage\Helper\File\Storage\Database
     */
    private $coreFileStorageDatabase;
    /**
     * @var Object
     */
    private $mediaDirectory;
    /**
     * @var \Magento\MediaStorage\Model\File\UploaderFactory
     */
    private $uploaderFactory;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;
    
    /**
     * @param \Magento\MediaStorage\Helper\File\Storage\Database $coreFileStorageDatabase
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\MediaStorage\Model\File\UploaderFactory $uploaderFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Magento\MediaStorage\Helper\File\Storage\Database $coreFileStorageDatabase,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\MediaStorage\Model\File\UploaderFactory $uploaderFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->coreFileStorageDatabase = $coreFileStorageDatabase;
        $this->mediaDirectory = $filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
        $this->uploaderFactory = $uploaderFactory;
        $this->storeManager = $storeManager;
        $this->logger = $logger;
        $this->baseTmpPath = "idproof/tmp/idproof";
        $this->basePath = "idproof/";
        $this->allowedExtensions= ['jpg', 'jpeg', 'gif', 'png'];
    }

    /**
     * SetBaseTmpPath function
     *
     * @param string $baseTmpPath
     * @return void
     */
    public function setBaseTmpPath($baseTmpPath)
    {
        $this->baseTmpPath = $baseTmpPath;
    }

    /**
     * SetBasePath function
     *
     * @param string $basePath
     * @return void
     */
    public function setBasePath($basePath)
    {
        $this->basePath = $basePath;
    }

    /**
     * SetAllowedExtensions function
     *
     * @param array $allowedExtensions
     * @return void
     */
    public function setAllowedExtensions($allowedExtensions)
    {
        $this->allowedExtensions = $allowedExtensions;
    }

    /**
     * GetBaseTmpPath function
     *
     * @return string
     */
    public function getBaseTmpPath()
    {
        return $this->baseTmpPath;
    }

    /**
     * GetBasePath function
     *
     * @return string
     */
    public function getBasePath()
    {
        return $this->basePath;
    }

    /**
     * GetAllowedExtensions function
     *
     * @return array
     */
    public function getAllowedExtensions()
    {
        return $this->allowedExtensions;
    }

    /**
     * GetFilePath function
     *
     * @param string $path
     * @param string $imageName
     * @return string
     */
    public function getFilePath($path, $imageName)
    {
        return rtrim($path, '/') . '/' . ltrim($imageName, '/');
    }

    /**
     * MoveFileFromTmp function
     *
     * @param string $imageName
     * @return string
     */
    public function moveFileFromTmp($imageName)
    {
        $baseTmpPath = $this->getBaseTmpPath();
        $basePath = $this->getBasePath();
        $baseImagePath = $this->getFilePath($basePath, $imageName);
        $baseTmpImagePath = $this->getFilePath($baseTmpPath, $imageName);
        try {
            $this->coreFileStorageDatabase->copyFile(
                $baseTmpImagePath,
                $baseImagePath
            );
            $this->mediaDirectory->renameFile(
                $baseTmpImagePath,
                $baseImagePath
            );
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Something went wrong while saving the file(s).')
            );
        }
        return $imageName;
    }

    /**
     * SaveFileToTmpDir function
     *
     * @param string $fileId
     * @return array
     */
    public function saveFileToTmpDir($fileId)
    {
        $baseTmpPath = $this->getBaseTmpPath();
        $uploader = $this->uploaderFactory->create(['fileId' => $fileId]);
        $uploader->setAllowedExtensions($this->getAllowedExtensions());
        $uploader->setAllowRenameFiles(true);
        $result = $uploader->save($this->mediaDirectory->getAbsolutePath($baseTmpPath));
        if (!$result) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('File can not be saved to the destination folder.')
            );
        }

        $result['tmp_name'] = str_replace('\\', '/', $result['tmp_name']);
        $result['path'] = str_replace('\\', '/', $result['path']);
        $result['url'] = $this->storeManager
                ->getStore()
                ->getBaseUrl(
                    \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
                ) . $this->getFilePath($baseTmpPath, $result['file']);
        $result['name'] = $result['file'];
        if (isset($result['file'])) {
            try {
                $relativePath = rtrim($baseTmpPath, '/') . '/' . ltrim($result['file'], '/');
                $this->coreFileStorageDatabase->saveFile($relativePath);
            } catch (\Exception $e) {
                $this->logger->critical($e);
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Something went wrong while saving the file(s).')
                );
            }
        }
        return $result;
    }
}
