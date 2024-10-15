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
namespace Webkul\MpVendorAttributeManager\Controller\Account;

use Magento\Customer\Controller\AbstractAccount;
use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\SessionFactory;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\App\RequestInterface;
use Webkul\Marketplace\Model\SellerFactory;
use Webkul\Marketplace\Model\ResourceModel\Seller\CollectionFactory;
use Webkul\Marketplace\Helper\Data;

/**
 * Webkul MpVendorAttributeManager Account BecomesellerPost Controller.
 */
class BecomesellerPost extends AbstractAccount
{
    /**
     * @var \Magento\Customer\Model\SessionFactory
     */
    protected $_customerSessionFactory;

    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    protected $_formKeyValidator;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    protected $_mediaDirectory;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $_customerRepository;

    /**
     * @var CustomerInterfaceFactory
     */
    protected $_customerDataFactory;

    /**
     * @var \Magento\MediaStorage\Model\File\UploaderFactory
     */
    protected $_fileUploaderFactory;

    /**
     * @var \Magento\Customer\Model\Customer\Mapper
     */
    protected $_customerMapper;

    /**
     * @var DataObjectHelper
     */
    protected $_dataObjectHelper;

    /**
     * @var  \Webkul\Marketplace\Model\SellerFactory
     */
    protected $_sellerFactory;
    
    /**
     * @var \Webkul\Marketplace\Model\ResourceModel\Seller\CollectionFactory
     */
    protected $sellerCollectionFactory;

    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    protected $mpHelper;

    /**
     * @param Context $context
     * @param SessionFactory $customerSessionFactory
     * @param FormKeyValidator $formKeyValidator
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param CustomerInterfaceFactory $customerDataFactory
     * @param Filesystem $filesystem
     * @param CustomerRepositoryInterface $customerRepository
     * @param DataObjectHelper $dataObjectHelper
     * @param \Magento\Customer\Model\Customer\Mapper $customerMapper
     * @param \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory
     * @param SellerFactory $sellerFactory
     * @param CollectionFactory $sellerCollectionFactory
     * @param Data $mpHelper
     */
    public function __construct(
        Context $context,
        SessionFactory $customerSessionFactory,
        FormKeyValidator $formKeyValidator,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        CustomerInterfaceFactory $customerDataFactory,
        Filesystem $filesystem,
        CustomerRepositoryInterface $customerRepository,
        DataObjectHelper $dataObjectHelper,
        \Magento\Customer\Model\Customer\Mapper $customerMapper,
        \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory,
        SellerFactory $sellerFactory,
        CollectionFactory $sellerCollectionFactory,
        Data $mpHelper
    ) {
        $this->customerSessionFactory = $customerSessionFactory;
        $this->_customerRepository = $customerRepository;
        $this->_customerDataFactory = $customerDataFactory;
        $this->_formKeyValidator = $formKeyValidator;
        $this->_customerMapper = $customerMapper;
        $this->_dataObjectHelper = $dataObjectHelper;
        $this->_date = $date;
        $this->_mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->_fileUploaderFactory = $fileUploaderFactory;
        $this->sellerFactory = $sellerFactory;
        $this->_sellerCollectionFactory = $sellerCollectionFactory;
        $this->mpHelper = $mpHelper;
        parent::__construct(
            $context
        );
    }

    /**
     * Check for Existing Shop Url
     *
     * @return boolean
     */
    private function isExistingShopUrl()
    {
        $shopUrl = $this->getRequest()->getParam("profileurl");
        $collection = $this->_sellerCollectionFactory->create();
        $collection->addFieldToFilter('shop_url', $shopUrl);
        if ($collection->getSize()) {
            return true;
        }

        return false;
    }

    /**
     * Get Approval Status
     *
     * @return int
     */
    private function getStatus()
    {
        if ($this->mpHelper->getIsPartnerApproval()) {
            return 0;
        }
        
        return 1;
    }

    /**
     * Save Seller Data
     */
    private function saveSellerData()
    {
        try {
            $customerId = $this->customerSessionFactory->create()->getCustomerId();
            $customerData = $this->getRequest()->getParams();

            $savedCustomerData = $this->_customerRepository->getById($customerId);
            $customer = $this->_customerDataFactory->create();
            $customerData = array_merge(
                $this->_customerMapper->toFlatArray($savedCustomerData),
                $customerData
            );
            $customerData['id'] = $customerId;
            if (!isset($customerData['is_vendor_group'])) {
                $customerData['is_vendor_group'] = 0;
            }
            $this->_dataObjectHelper->populateWithArray(
                $customer,
                $customerData,
                \Magento\Customer\Api\Data\CustomerInterface::class
            );
            $this->_customerRepository->save($customer);
            
            $shopUrl = $this->getRequest()->getParam("profileurl");
            $sellerId = $this->customerSessionFactory->create()->getCustomerId();
            $status = $this->getStatus();
            $autoId = 0;
            $collection = $this->_sellerCollectionFactory->create();
            $collection->addFieldToFilter('seller_id', $sellerId);
            foreach ($collection as $value) {
                $autoId = $value->getId();
                break;
            }

            $seller = $this->sellerFactory->create()->load($autoId);
            $seller->setData('is_seller', $status);
            $seller->setData('shop_url', $shopUrl);
            $seller->setData('seller_id', $sellerId);
            $seller->setCreatedAt($this->_date->gmtDate());
            $seller->setUpdatedAt($this->_date->gmtDate());
            $seller->setAdminNotification(1);
            $seller->save();
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }
        
        try {
            if ($this->getStatus()) {
                /* clear cache */
                $this->mpHelper->clearCache();
                $this->messageManager->addSuccess(
                    __('Congratulations! Your seller account is created.')
                );
            } else {
                $this->messageManager->addSuccess(
                    __('Your request to become seller is successfully raised.')
                );
            }
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }
    }

    /**
     * BecomesellerPost action.
     *
     * @return \Magento\Framework\Controller\Result\RedirectFactory
     */
    public function execute()
    {
        $hasError = false;
        /**
         * @var \Magento\Framework\Controller\Result\Redirect
         */
        $resultRedirect = $this->resultRedirectFactory->create();
        if (!$this->_formKeyValidator->validate($this->getRequest())) {
            return $this->resultRedirectFactory->create()->setPath(
                'marketplace/account/becomeseller',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }

        if (!$this->getRequest()->isPost()) {
            return $this->resultRedirectFactory->create()->setPath(
                'marketplace/account/becomeseller',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }

        try {
            if (empty($this->getRequest()->getParam("is_seller"))) {
                $this->messageManager->addError(
                    __('Please confirm that you want to become seller.')
                );
                return $this->resultRedirectFactory->create()->setPath(
                    'marketplace/account/becomeseller',
                    ['_secure' => $this->getRequest()->isSecure()]
                );
            }

            if ($this->isExistingShopUrl()) {
                $this->messageManager->addError(
                    __('Shop URL already exist please set another.')
                );
                return $this->resultRedirectFactory->create()->setPath(
                    'marketplace/account/becomeseller',
                    ['_secure' => $this->getRequest()->isSecure()]
                );
            }

            $this->saveSellerData();
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }

        return $this->resultRedirectFactory->create()->setPath(
            'marketplace/account/becomeseller',
            ['_secure' => $this->getRequest()->isSecure()]
        );
    }
}
