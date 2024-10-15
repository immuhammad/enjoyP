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
namespace Webkul\MpVendorAttributeManager\Controller\Seller;

use Magento\Customer\Controller\AbstractAccount;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Filesystem;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Customer\Model\Customer\Mapper;
use Magento\Customer\Model\SessionFactory;
use Magento\Customer\Model\AttributeFactory;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Webkul\MpVendorAttributeManager\Helper\Data;
use Webkul\MpVendorAttributeManager\Model\ResourceModel\VendorAttribute\CollectionFactory;

class SaveAttribute extends \Magento\Customer\Controller\AbstractAccount
{

    /**
     * @var /Magento\Customer\Api\Data\CustomerInterfaceFactory
     */
    protected $_customerDataFactory;

    /**
     * @var /Magento\Framework\Api\DataObjectHelper
     */
    protected $_dataObjectHelper;

    /**
     * @var \Magento\Customer\Model\Customer\Mapper
     */
    protected $_customerMapper;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $filesystem;

    /**
     * @var \Magento\Customer\Model\AttributeFactory
     */
    protected $attributeFactory;

    /**
     * @var \Magento\MediaStorage\Model\File\UploaderFactory
     */
    protected $fileUploaderFactory;

    /**
     * @var \Webkul\MpVendorAttributeManager\Helper\Data
     */
    protected $helper;

    /**
     * @var \Webkul\MpVendorAttributeManager\Model\ResourceModel\VendorAttribute\CollectionFactory
     */
    protected $vendorAttributeCollectionFactory;

    /**
     * @var \Magento\Customer\Model\SessionFactory
     */
    protected $customerSessionFactory;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $_customerRepository;

    /**
     * @param Context $context
     * @param CustomerInterfaceFactory $customerDataFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param Mapper $customerMapper
     * @param CustomerRepositoryInterface $customerRepository
     * @param SessionFactory $customerSessionFactory
     * @param Filesystem $filesystem
     * @param AttributeFactory $attributeFactory
     * @param UploaderFactory $fileUploaderFactory
     * @param Data $helper
     * @param CollectionFactory $vendorAttributeCollectionFactory
     */
    public function __construct(
        Context $context,
        CustomerInterfaceFactory $customerDataFactory,
        DataObjectHelper $dataObjectHelper,
        Mapper $customerMapper,
        CustomerRepositoryInterface $customerRepository,
        SessionFactory $customerSessionFactory,
        Filesystem $filesystem,
        AttributeFactory $attributeFactory,
        UploaderFactory $fileUploaderFactory,
        Data $helper,
        CollectionFactory $vendorAttributeCollectionFactory
    ) {
        $this->customerSessionFactory = $customerSessionFactory;
        $this->_customerRepository = $customerRepository;
        $this->_customerDataFactory = $customerDataFactory;
        $this->_customerMapper = $customerMapper;
        $this->_dataObjectHelper = $dataObjectHelper;
        $this->filesystem = $filesystem;
        $this->attributeFactory = $attributeFactory;
        $this->fileUploaderFactory = $fileUploaderFactory;
        $this->helper = $helper;
        $this->vendorAttributeCollectionFactory = $vendorAttributeCollectionFactory;
        parent::__construct($context);
    }

    /**
     * Function save vendor attributes.
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $paramData = $this->getRequest();
        $customerId = $this->customerSessionFactory->create()->getCustomerId();

        $vendorAttributeCollection = $this->vendorAttributeCollectionFactory->create()
                                        ->getVendorAttributeCollection();

        $error = [];
        $sellerData = $paramData->getPostValue();

        $sellerData = $this->setBooleanData($sellerData);

        foreach ($vendorAttributeCollection as $vendorAttribute) {
            foreach ($sellerData as $attributeCode => $attributeValue) {
                if ($attributeCode==$vendorAttribute->getAttributeCode()) {
                    if ($vendorAttribute->getIsRequired() && empty($attributeValue)) {
                        $error[] = $vendorAttribute->getAttributeCode();
                    }
                }
            }
        }
        if (!empty($error)) {
            $this->messageManager->addError(__('Vendor Required Attributes can\'t be Empty.'));
        } else {
            $savedCustomerData = $this->_customerRepository->getById($customerId);
            $saveData = $this->_customerMapper->toFlatArray($savedCustomerData);

            $customer = $this->_customerDataFactory->create();

            $sellerData = array_merge(
                $this->_customerMapper->toFlatArray($savedCustomerData),
                $sellerData
            );
            $sellerData['id'] = $customerId;
            $files = $this->getRequest()->getFiles();
            try {
                foreach ($files as $fileAttributeCode => $value) {
                    if ($value['error'] == 0) {
                        $result = $this->uploadFileForAttribute($fileAttributeCode);
                        $sellerData[$fileAttributeCode] = $result['file'];
                    }
                }

                $this->_dataObjectHelper->populateWithArray(
                    $customer,
                    $sellerData,
                    \Magento\Customer\Api\Data\CustomerInterface::class
                );
                $customer->setData('ignore_validation_flag', true);
                $this->_customerRepository->save($customer);
                $this->messageManager->addSuccess(__('Vendor Attributes has been saved.'));
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }
        return $resultRedirect->setPath('marketplace/account/editprofile/');
    }

    /**
     * Set Values for Boolean Type Attribute
     *
     * @param Array $sellerData
     *
     * @return Array $sellerData
     */
    protected function setBooleanData($sellerData)
    {
        $vendorAttributeType = [0,2];
        $booleanAttributes = $this->vendorAttributeCollectionFactory->create()
                                ->getVendorAttributeCollection()
                                ->addFieldToFilter("frontend_input", "boolean")
                                ->addFieldToFilter("wk_attribute_status", 1)
                                ->addFieldToFilter("attribute_used_for", ["in" => $vendorAttributeType]);

        if ($booleanAttributes->getSize()) {
            foreach ($booleanAttributes as $attribute) {
                $attributeCode = $attribute->getAttributeCode();
                $attributeValue = (boolean)$this->getRequest()->getParam($attributeCode, false);
                $sellerData[$attributeCode] = $attributeValue;
            }
        }
        return $sellerData;
    }

    /**
     * Upload image and save values for file type Attributes
     *
     * @param String $attributeCode
     *
     * @return Array $result
     */
    protected function uploadFileForAttribute($attributeCode)
    {
        $path = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA)
                         ->getAbsolutePath('vendorfiles/');
        $attributeType = $this->attributeFactory->create()
                            ->load($attributeCode, "attribute_code")
                            ->getFrontendInput();

        $allowedExtensions =  explode(',', $this->helper->getConfigData('allowede_'.$attributeType.'_extension'));
        $uploader = $this->fileUploaderFactory->create(['fileId' => $attributeCode]);
        $uploader->setAllowedExtensions($allowedExtensions);
        $uploader->setAllowRenameFiles(true);
        $uploader->setFilesDispersion(false);
        $result = $uploader->save($path.$attributeType);
        return $result;
    }
}
