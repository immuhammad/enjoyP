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

namespace Webkul\MpVendorAttributeManager\Controller\Marketplace\Wysiwyg\Gallery;

class Upload extends \Webkul\Marketplace\Controller\Wysiwyg\Gallery\Upload
{
    /**
     * Execute action
     *
     * @return void
     */
    public function execute()
    {
        $helper = $this->mpHelper;
        $isPartner = $helper->isSeller();
        $sellerId = $helper->getCustomerId();
        try {
            $target = $this->mediaDirectory->getAbsolutePath(
                'tmp/desc'
            );
            $fileUploader = $this->fileUploaderFactory->create(
                ['fileId' => 'image']
            );
            $fileUploader->setAllowedExtensions(
                ['gif', 'jpg', 'png', 'jpeg']
            );
            $fileUploader->setFilesDispersion(true);
            $fileUploader->setAllowRenameFiles(true);
            $resultData = $fileUploader->save($target);
            unset($resultData['tmp_name']);
            unset($resultData['path']);
            $resultData['url'] = $this->storeManager->getStore()->getBaseUrl(
                \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
            ) . 'tmp/desc' . '/' . ltrim(str_replace('\\', '/', $resultData['file']), '/');
            $resultData['file'] = $resultData['file'] . '.tmp';
            $checkVal = $this->saveImageDesc($sellerId, $resultData['url'], $resultData['file']);
            $this->getResponse()->representJson(
                $this->jsonHelper->jsonEncode($resultData)
            );
        } catch (\Exception $e) {
            $helper->logDataInLogger(
                "Controller_Wysiwyg_Gallery_Upload execute : ".$e->getMessage()
            );
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
