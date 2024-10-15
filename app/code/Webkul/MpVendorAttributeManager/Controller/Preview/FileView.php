<?php

/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpVendorAttributeManager
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpVendorAttributeManager\Controller\Preview;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Framework\Exception\NotFoundException;

class FileView extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\Controller\Result\RawFactory
     */
    protected $resultRawFactory;

    /**
     * @var \Magento\Framework\Url\DecoderInterface
     */
    protected $urlDecoder;

    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    protected $fileFactory;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     * @param \Magento\Framework\Url\DecoderInterface $urlDecoder
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     * @param \Magento\Framework\Filesystem $fileSystem
     * @param \Magento\MediaStorage\Helper\File\Storage $fileStorage
     * @param \Magento\Framework\Filesystem\Io\File $file
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Magento\Framework\Url\DecoderInterface $urlDecoder,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\Filesystem $fileSystem,
        \Magento\MediaStorage\Helper\File\Storage $fileStorage,
        \Magento\Framework\Filesystem\Io\File $file
    ) {
        $this->resultRawFactory    = $resultRawFactory;
        $this->urlDecoder  = $urlDecoder;
        $this->fileFactory = $fileFactory;
        $this->fileSystem = $fileSystem;
        $this->fileStorage = $fileStorage;
        $this->file = $file;
        return parent::__construct($context);
    }

    /**
     * View action
     *
     * @return \Magento\Framework\View\Result\PageFactory
     */
    public function execute()
    {
        $file = null;
        $plain = false;
        if ($this->getRequest()->getParam('file')) {
            // download file
            $file = $this->urlDecoder->decode(
                $this->getRequest()->getParam('file')
            );
        } elseif ($this->getRequest()->getParam('image')) {
            // show plain image
            $file = $this->urlDecoder->decode(
                $this->getRequest()->getParam('image')
            );
            $plain = true;
        } else {
            throw new NotFoundException(__('Page not found.'));
        }

        /** @var \Magento\Framework\Filesystem $filesystem */
        $directory = $this->fileSystem->getDirectoryRead(DirectoryList::MEDIA);
        $fileName = 'vendorfiles/' . ltrim($file, '/');
        $path = $directory->getAbsolutePath($fileName);
        $pathInfo = $this->file->getPathInfo($path);

        if (!$directory->isFile($fileName)
            && !$this->fileStorage->processStorageFile($path)
        ) {
            throw new NotFoundException(__('Page not found.'));
        }
        if ($plain) {
            $extension = $pathInfo['extension'];
            switch (strtolower($extension)) {
                case 'gif':
                    $contentTypeValue = 'image/gif';
                    break;
                case 'jpg':
                    $contentTypeValue = 'image/jpeg';
                    break;
                case 'png':
                    $contentTypeValue = 'image/png';
                    break;
                default:
                    $contentTypeValue = 'application/octet-stream';
                    break;
            }
            $stat = $directory->stat($fileName);
            $contentLength = $stat['size'];
            $contentModify = $stat['mtime'];
            /** @var \Magento\Framework\Controller\Result\Raw $resultRaw */
            $resultRaw = $this->resultRawFactory->create();
            $resultRaw->setHttpResponseCode(200)
                ->setHeader('Pragma', 'public', true)
                ->setHeader('Content-type', $contentTypeValue, true)
                ->setHeader('Content-Length', $contentLength)
                ->setHeader('Last-Modified', date('r', $contentModify));
            $resultRaw->setContents($directory->readFile($fileName));
            
            return $resultRaw;
        } else {
            $name = $pathInfo['basename'];
            $this->fileFactory->create(
                $name,
                ['type' => 'filename', 'value' => $fileName],
                DirectoryList::MEDIA
            );
        }
    }
}
