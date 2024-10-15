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
namespace Webkul\MpVendorAttributeManager\Model\VendorAttribute\Attribute\Backend;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Customer\Controller\RegistryConstants;
use Magento\Framework\Filesystem\DriverInterface;

class Image extends \Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend
{
    /**
     * @var string
     */
    protected $_type = 'image';

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $_filesystem;
    
    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;

    /**
     * @var \Webkul\MpVendorAttributeManager\Helper\Data
     */
    protected $_fileUploaderFactory;

    /**
     * @var \Magento\MediaStorage\Model\File\UploaderFactory
     */
    protected $_currentHelper;

    /**
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Webkul\MpVendorAttributeManager\Helper\Data $currentHelper
     * @param \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\RequestInterface $request,
        \Webkul\MpVendorAttributeManager\Helper\Data $currentHelper,
        \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory
    ) {
        $this->_logger = $logger;
        $this->_filesystem = $filesystem;
        $this->_coreRegistry = $registry;
        $this->request = $request;
        $this->_fileUploaderFactory = $fileUploaderFactory;
        $this->_currentHelper = $currentHelper;
    }

    /**
     * Save uploaded file and set its name to category
     *
     * @param \Magento\Framework\DataObject $object
     * @return \Magento\Catalog\Model\Category\Attribute\Backend\Image
     */
    public function afterSave($object)
    {
        $attributeCode = $this->getAttribute()->getName();
        $attributeType = $this->getAttribute()->getFrontendInput();
        $value = $this->request->getPostValue();
        $explodedValue = explode('_', $attributeCode);
        if ($attributeType == $this->_type && is_array($explodedValue)) {
            if ('wkv' == $explodedValue[0]) {
                $savedValue = '';
                if (isset($value['customer'][$attributeCode])) {
                    $savedValue = $value['customer'][$attributeCode];
                }
                if (isset($value[$attributeCode]['delete']) && $value[$attributeCode]['delete'] == 1) {
                    $object->setData($attributeCode, '');
                    $this->getAttribute()->getEntity()->saveAttribute($object, $attributeCode);
                    return $this;
                }

                $path = $this->_filesystem->getDirectoryRead(
                    DirectoryList::MEDIA
                )->getAbsolutePath(
                    'vendorfiles/image/'
                );
                if (is_array($value) && !empty($value['delete'])) {
                      $object->setData($this->getAttribute()->getName(), '');
                      $this->getAttribute()->getEntity()->saveAttribute($object, $attributeCode);
                      return $this;
                }
                $allowedImageExtensions = $this->_currentHelper->getConfigData('allowede_'.$this->_type.'_extension');
                $allowedExtensions = explode(',', $allowedImageExtensions);

                try {
                    $uploader = $this->_fileUploaderFactory->create(['fileId' => $attributeCode]);
                    $uploader->setAllowedExtensions($allowedExtensions);
                    $uploader->setAllowRenameFiles(true);
                    $uploader->setFilesDispersion(false);
                    $result = $uploader->save($path);
                    $object->setData($attributeCode, $result['file']);
                    $this->getAttribute()->getEntity()->saveAttribute($object, $attributeCode);
                } catch (\Exception $e) {
                    // if no image was set - save previous image value
                    $filteredSavedValue = "";
                    if (is_array($savedValue)) {
                        $filteredSavedValue =  $savedValue[0]['file'];
                        if ($filteredSavedValue != '') {
                            $object->setData($attributeCode, $filteredSavedValue);
                            $this->getAttribute()->getEntity()->saveAttribute($object, $attributeCode);
                        }
                    } elseif ($savedValue != '') {
                        $object->setData($attributeCode, $savedValue);
                        $this->getAttribute()->getEntity()->saveAttribute($object, $attributeCode);
                    }
                    return $this;
                }
            }
        }
        return $this;
    }
}
