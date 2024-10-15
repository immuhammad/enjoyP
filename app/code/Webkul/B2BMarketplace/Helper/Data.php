<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_B2BMarketplace
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\B2BMarketplace\Helper;

use Magento\Customer\Model\Customer;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;
use Magento\Framework\App\Area;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory as CustomerCollectionFactory;
use Webkul\B2BMarketplace\Model\ResourceModel\Category\CollectionFactory as B2bCategoryCollectionFactory;
use Webkul\B2BMarketplace\Api\QuoteManagementInterface;
use Webkul\Marketplace\Model\SaleperpartnerFactory as MpSalesPartner;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const B2B_TYPE_PENDING = 0;

    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     * @var B2bCategoryCollectionFactory
     */
    private $b2bCategoryCollectionFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    private $timezone;

    /**
     * @var \Webkul\B2BMarketplace\Logger\Logger
     */
    private $logger;

    /**
     * @var QuoteManagementInterface
     */
    private $b2bQuoteManagement;

    /**
     * @param \Magento\Framework\App\Helper\Context $context,
     * @param CustomerCollectionFactory $customerCollectionFactory,
     * @param \Webkul\Marketplace\Helper\Data $mpHelper,
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager,
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory,
     * @param \Magento\Customer\Model\SessionFactory $customerSessionFactory,
     * @param \Magento\Directory\Block\Data $directoryBlock,
     * @param AttributeSetFactory $attributeSetFactory,
     * @param \Magento\Framework\Message\ManagerInterface $messageManager,
     * @param \Webkul\Marketplace\Model\SellerFactory $sellerFactory,
     * @param Config $eavConfig,
     * @param \Magento\Framework\App\ResourceConnection $resource,
     * @param \Webkul\B2BMarketplace\Model\MessageFactory $messageFactory,
     * @param \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory,
     * @param CategoryRepositoryInterface $categoryRepository,
     * @param B2bCategoryCollectionFactory $b2bCategoryCollectionFactory,
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezoneInterface
     * @param \Webkul\B2BMarketplace\Logger\Logger $logger
     * @param Filesystem $filesystem
     * @param QuoteManagementInterface $b2bQuoteManagement
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        CustomerCollectionFactory $customerCollectionFactory,
        \Webkul\Marketplace\Helper\Data $mpHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Model\SessionFactory $customerSessionFactory,
        \Magento\Directory\Block\Data $directoryBlock,
        AttributeSetFactory $attributeSetFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Webkul\Marketplace\Model\SellerFactory $sellerFactory,
        Config $eavConfig,
        \Magento\Framework\App\ResourceConnection $resource,
        \Webkul\B2BMarketplace\Model\MessageFactory $messageFactory,
        \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory,
        CategoryRepositoryInterface $categoryRepository,
        B2bCategoryCollectionFactory $b2bCategoryCollectionFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezoneInterface,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Webkul\B2BMarketplace\Logger\Logger $logger,
        Filesystem $filesystem,
        QuoteManagementInterface $b2bQuoteManagement,
        MpSalesPartner $mpSalesPartner = null,
        \Magento\Cms\Helper\Wysiwyg\Images $wysiwygImages = null
    ) {
        $this->_customerCollectionFactory = $customerCollectionFactory;
        $this->_mpHelper = $mpHelper;
        $this->_storeManager = $storeManager;
        $this->_customerFactory = $customerFactory;
        $this->_customerSessionFactory = $customerSessionFactory;
        $this->_directoryBlock = $directoryBlock;
        $this->_messageManager = $messageManager;
        $this->_sellerFactory = $sellerFactory;
        $this->_request = $context->getRequest();
        $this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->_eavConfig = $eavConfig;
        $this->_resource = $resource;
        $this->_messageFactory = $messageFactory;
        $this->_attributeSetFactory = $attributeSetFactory;
        $this->_fileUploaderFactory = $fileUploaderFactory;
        $this->_mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->_eavEntity = $this->_objectManager->create(\Magento\Eav\Model\Entity::class);
        $this->_customerRepository = $this->_objectManager
        ->create(\Magento\Customer\Api\CustomerRepositoryInterface::class);
        $this->_customerDataFactory = $this->_objectManager
        ->create(\Magento\Customer\Api\Data\CustomerInterfaceFactory::class);
        $this->_customerMapper = $this->_objectManager->create(\Magento\Customer\Model\Customer\Mapper::class);
        $this->_dataObjectHelper = $this->_objectManager->create(\Magento\Framework\Api\DataObjectHelper::class);
        $this->categoryRepository = $categoryRepository;
        $this->b2bCategoryCollectionFactory = $b2bCategoryCollectionFactory;
        $this->timezone = $timezoneInterface;
        $this->_date = $date;
        $this->logger = $logger;
        $this->b2bQuoteManagement = $b2bQuoteManagement;
        $this->mpSalesPartner = $mpSalesPartner ?: \Magento\Framework\App\ObjectManager::getInstance()
        ->create(MpSalesPartner::class);
        $this->wysiwygImages = $wysiwygImages ?: \Magento\Framework\App\ObjectManager::getInstance()
        ->create(\Magento\Cms\Helper\Wysiwyg\Images::class);
        parent::__construct($context);
    }

    public function isUserExist($email)
    {
        $collection = $this->_customerCollectionFactory->create();
        $collection->addFieldToFilter("email", $email);
        if ($collection->getSize()) {
            return true;
        }

        return false;
    }

    public function isUserLoggedIn()
    {
        return $this->_mpHelper->isCustomerLoggedIn();
    }

    public function isSeller()
    {
        return $this->_mpHelper->isSeller();
    }

    public function getMinimumOrderValue()
    {
        $minOrderAmount = '';
        $sellerId = $this->_mpHelper->getCustomerId();
        $salePerPartnerModel = $this->mpSalesPartner->create()
                                    ->getCollection()
                                    ->addFieldToFilter('seller_id', $sellerId)
                                    ->addFieldToFilter('min_order_status', 1);
        if ($salePerPartnerModel->getSize()) {
            $minOrderAmount = $salePerPartnerModel->getFirstItem()
                                                ->getMinOrderAmount();
        }
        return $minOrderAmount;
    }

    /**
     * @return bool
     */
    public function isMagentoSwatchesModuleInstalled()
    {
        return $this->_moduleManager->isEnabled('Magento_Swatches');
    }

    public function validateSupplierRegistrationData($data)
    {
        $fields = [
            "email" => "Email Address",
            "password" => "Password",
            "password_confirmation" => "Password",
            "firstname" => "First Name",
            "lastname" => "Last Name",
            "telephone" => "Phone Number",
            "company" => "Company",
            "company_url" => "Company Url",
            "country_id" => "Country",
            "city" => "City",
            "street" => "Address",
            "postcode" => "Postcode"
        ];
        if (isset($data['street']) && is_array($data['street'])) {
            $data['street'] = implode(',', $data['street']);
        }
        if (isset($data['region'])) {
            $fields['region'] = "State";
        }
        if (isset($data['region_id'])) {
            $fields['region_id'] = "State";
        }

        return $this->validateData($data, $fields);
    }

    public function validateData($data, $fields)
    {
        $result = [];
        $result["error"] = false;
        $result["error_list"] = [];
        $result["data"] = [];
        foreach ($fields as $field => $label) {
            if (!array_key_exists($field, $data)) {
                $result["error"] = true;
                $result["error_list"][] = __("%1 is required field.", $label);
            } else {
                $value = trim($data[$field]);

                if ($field == "region" || $field == "region_id") {
                    if ($value == "" && !isset($fields['region_id']) && empty($fields['region_id'])) {
                        $result["error"] = true;
                        $result["error_list"][] = __("%1 is required field.", $label);
                    }
                } else {
                    if ($value == "") {
                        $result["error"] = true;
                        $result["error_list"][] = __("%1 is required field.", $label);
                    }
    
                    if ($field == "company_url" && $this->isShopUrlExist($value)) {
                        $result["error"] = true;
                        $result["error_list"][] = __("%1 is already exist.", $label);
                    }
    
                    if ($field == "email" && $this->isUserExist($value)) {
                        $result["error"] = true;
                        $result["error_list"][] = __("%1 is already exist.", $label);
                    }
                }

                $result["data"][$field] = $value;
            }
        }

        return $result;
    }

    public function isShopUrlExist($shopUrl)
    {
        $storeId = $this->_storeManager->getStore()->getId();
        $collection = $this->_sellerFactory
                            ->create()
                            ->getCollection()
                            ->addFieldToFilter('shop_url', $shopUrl);
        $collection->addFieldToFilter('store_id', $storeId);

        if ($collection->getSize()) {
            return true;
        }

        return false;
    }

    public function getCustomerList()
    {
        $supplierId = $this->getSupplierId();
        $model = $this->_messageFactory->create();
        $collection = $model->getCollection()
            ->getCustomerList($supplierId);

        return $collection;
    }

    public function getSupplierList()
    {
        $customerId = $this->getCustomerId();
        $model = $this->_messageFactory->create();
        $collection = $model->getCollection()
            ->getSupplierList($customerId);

        return $collection;
    }

    public function getCustomerTotalUnseenMsg($listId)
    {
        $model = $this->_messageFactory->create();
        $resultData = $model->getCollection()->getCustomerTotalUnseenMsgData($listId);
        if (isset($resultData[0])) {
            return $resultData[0];
        } else {
            return 0;
        }
    }

    public function getSupplierTotalUnseenMsg($listId)
    {
        $model = $this->_messageFactory->create();
        $resultData = $model->getCollection()->getSupplierTotalUnseenMsgData($listId);
        if (isset($resultData[0])) {
            return $resultData[0];
        } else {
            return 0;
        }
    }

    public function getCustomerId()
    {
        return $this->_customerSessionFactory->create()->getCustomerId();
    }

    public function getSupplierId()
    {
        return (int) $this->_mpHelper->getCustomerId();
    }

    /**
     * Fetch all messages from customers to supplier at supplier dashboard
     */
    public function fetchCustomerMessages($customerId, $supplierId = 0)
    {
        if ($supplierId == 0) {
            $supplierId = $this->getSupplierId();
        }

        $model = $this->_messageFactory->create();
        $collection = $model->getCollection();

        $sql = "";
        $sql .= "(sender_id = $supplierId and receiver_id = $customerId)";
        $sql .= " or ";
        $sql .= "(sender_id = $customerId and receiver_id = $supplierId)";
        $collection->getSelect()->where($sql);
        $collection->getSelect()->order("id asc");

        $customer = $this->_objectManager->create(
            \Magento\Customer\Model\Customer::class
        )->load($customerId);
        $result = [];
        $chat = [];
        foreach ($collection as $item) {
            $msg = [];
            $messageId = $item->getId();
            $msg['id'] = $messageId;
            $msg['msg'] = $item->getMsg();
            $msg['date'] = $this->formatDate($item->getCreatedAt(), \IntlDateFormatter::LONG, true);
            $msg['time'] = $this->formatDate($item->getCreatedAt(), \IntlDateFormatter::LONG, true);
            $msg['name'] = "";
            if ($item->getType() == \Webkul\B2BMarketplace\Helper\Quote::TYPE_BUYER) {
                $msg['name'] = $customer->getFirstname();
                $item->setSeenStatus(1)->save();
            }

            $attachmentsColl = $this->getAttachments($messageId);

            $attachmentsArr = [];
            foreach ($attachmentsColl as $value) {
                $link = $this->getSupplierAttachmentUrl($value->getId());
                $fileName = $value->getFileName();
                $attachmentsArr[] = '<a class="wk-msg-attachment" href="'.$link.'">'.$fileName.'</a>';
            }
            $msg['attachments'] = implode('', $attachmentsArr);
            $chat[] = $msg;
        }

        $result['chat'] = $chat;
        $img = $this->getImg($customer);
        $result['img'] = $img;
        $result['name'] = $customer->getFirstname();
        $result['type'] = __("Customer");
        return $result;
    }

    /**
     * Returns collection of message attachments.
     *
     * @param int $messageId
     * @return array|\Webkul\B2BMarketplace\Model\ResourceModel\Attachment\Collection
     */
    public function getAttachments($messageId)
    {
        if ($messageId) {
            return $this->b2bQuoteManagement->getAttachments($messageId);
        }
        return [];
    }

    /**
     * Returns supplier message attachment URL.
     *
     * @param int $attachmentId
     * @return string
     */
    public function getSupplierAttachmentUrl($attachmentId)
    {
        return $this->_storeManager->getStore()->getUrl(
            'b2bmarketplace/supplier_attachment/download',
            [
                'attachmentId' => $attachmentId
            ]
        );
    }

    /**
     * Returns customer message attachment URL.
     *
     * @param int $attachmentId
     * @return string
     */
    public function getCustomerAttachmentUrl($attachmentId)
    {
        return $this->_storeManager->getStore()->getUrl(
            'b2bmarketplace/customer_attachment/download',
            [
                'attachmentId' => $attachmentId
            ]
        );
    }

    /**
     * Fetch all messages from suppliers to customer at customer dashboard
     */
    public function fetchSupplierMessages($supplierId, $customerId = 0)
    {
        if ($customerId == 0) {
            $customerId = $this->getCustomerId();
        }

        $model = $this->_messageFactory->create();
        $collection = $model->getCollection();
        $sql = "(sender_id = $customerId and receiver_id = $supplierId) or ".
        "(sender_id = $supplierId and receiver_id = $customerId)";
        $collection->getSelect()->where($sql);
        $collection->getSelect()->order("id asc");
        $supplier = $this->_objectManager->create(\Magento\Customer\Model\Customer::class)->load($supplierId);
        $result = [];
        $chat = [];
        foreach ($collection as $item) {
            $msg = [];
            $messageId = $item->getId();
            $msg['id'] = $messageId;
            $msg['msg'] = $item->getMsg();
            $msg['date'] = $this->formatDate($item->getCreatedAt(), \IntlDateFormatter::LONG, true);
            $msg['time'] = $this->formatDate($item->getCreatedAt(), \IntlDateFormatter::LONG, true);
            $msg['name'] = "";
            if ($item->getType() == \Webkul\B2BMarketplace\Helper\Quote::TYPE_SUPPLIER) {
                $msg['name'] = $supplier->getFirstname();
                $item->setSeenStatus(1)->save();
            }

            $attachmentsColl = $this->getAttachments($messageId);

            $attachmentsArr = [];
            foreach ($attachmentsColl as $value) {
                $link = $this->getCustomerAttachmentUrl($value->getId());
                $fileName = $value->getFileName();
                $attachmentsArr[] = '<a class="wk-msg-attachment" href="'.$link.'">'.$fileName.'</a>';
            }
            $msg['attachments'] = implode('', $attachmentsArr);
            $chat[] = $msg;
        }

        $result['chat'] = $chat;

        $img = $this->getImg($supplier);
        $result['img'] = $img;
        $result['name'] = $supplier->getFirstname();
        $result['type'] = "Supplier";
        return $result;
    }

    public function getBaseUrl()
    {
        return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB);
    }

    public function getImg($customer, $type = 0)
    {
        $icons = [];
        $icons[] = "captain-america.png";
        $icons[] = "groot.png";
        $icons[] = "hellboy.png";
        $icons[] = "spiderman.png";
        $icons[] = "wolvorine.png";
        $icons[] = "ironman.png";
        $icons[] = "flash.png";
        $icons[] = "deadpool.png";
        $icons[] = "cyclops.png";
        $icons[] = "batman.png";
        $icons[] = "c3pro.png";
        $icons[] = "magneto.png";
        $icons[] = "daredevil.png";
        $icons[] = "antman.png";
        $icons[] = "loki.png";
        $icons[] = "hawkeye.png";
        if ($type == 1) {
            $no = $customer->getCustomerId()%15;
        } elseif ($type == 2) {
            $no = $customer->getSupplierId()%15;
        } else {
            $no = $customer->getId()%15;
        }

        $img = $icons[$no];
        $field = 'Webkul_B2BMarketplace/images/icons/'.$img;
        return $this->getViewFileUrl($field);
    }

    public function sendMessage($msg, $senderId, $receiverId, $type = 2)
    {
        $model = $this->_messageFactory->create();
        $data = [];
        $data['sender_id'] = $senderId;
        $data['receiver_id'] = $receiverId;
        $data['msg'] = $msg;
        $data['type'] = $type;
        $msg = $model->setData($data)->save();
        $msg = $model->load($msg->getId());

        $currentDate = $this->formatDate(date('Y-m-d H:i:s'), \IntlDateFormatter::LONG, true);

        /** set response_rate_cal_flag to 0 if supplier already replied to it within his re*/
        if ($type == 1) {
            $flag = 0;
            $customer = $this->getCustomer($senderId);
            $responseTime = (int)$customer->getWkSupplierResponseTime();
            $responseTimeUnit = $customer->getWkSupplierResponseTimeUnit();
            if (!$responseTimeUnit) {
                $responseTimeUnit = 'hour';
            }
            $msgCollections = $this->_messageFactory->create()
                ->getCollection()
                ->addFieldToFilter("list_id", $msg->getListId())
                ->addFieldToFilter('type', 0);
            foreach ($msgCollections as $msgCollection) {
                $createdDate = date_create($msgCollection->getCreatedAt());
                date_modify($createdDate, '+'.$responseTime." ".$responseTimeUnit);
                $maxRespnseDate = date_format($createdDate, 'Y-m-d H:i:s');
                if (strtotime($currentDate) <= strtotime($maxRespnseDate)) {
                    $msgCollection->setResponseRateCalFlag(0)->save();
                    $flag = 1;
                }
            }
            if ($flag) {
                $model->setResponseRateCalFlag(0)->save();
            }
        }

        if ($type == 1) {
            if ($senderId == 0) {
                $sender = "Admin";
                $senderName = $this->getAdminName();
                $senderEmail = $this->getAdminEmail();
            } else {
                $sender = "Supplier";
                $customer = $this->getCustomer($senderId);
                $senderName = $customer->getFirstname()." ".$customer->getLastname();
                $senderEmail = $customer->getEmail();
            }

            $customer = $this->getCustomer($receiverId);
            $receiverName = $customer->getFirstname()." ".$customer->getLastname();
            $receiverEmail = $customer->getEmail();
        } else {
            $sender = "Buyer";
            $customer = $this->getCustomer($senderId);
            $senderName = $customer->getFirstname()." ".$customer->getLastname();
            $senderEmail = $customer->getEmail();

            if ($receiverId == 0) {
                $receiverName = $this->getAdminName();
                $receiverEmail = $this->getAdminEmail();
            } else {
                $customer = $this->getCustomer($receiverId);
                $receiverName = $customer->getFirstname()." ".$customer->getLastname();
                $receiverEmail = $customer->getEmail();
            }
        }
        $templateVars = [];
        $templateVars["name"] = $receiverName;
        $templateVars["msg"] = $data['msg'];
        $templateVars["sender"] = $sender;

        $senderDetails = [];
        $senderDetails["name"] = $senderName;
        $senderDetails["email"] = $senderEmail;

        $receiverDetails = [];
        $receiverDetails["name"] = $receiverName;
        $receiverDetails["email"] = $receiverEmail;

        $this->_objectManager->create(
            'Webkul\B2BMarketplace\Helper\Email'
        )->newMessageEmail($templateVars, $senderDetails, $receiverDetails);

        return $msg;
    }

    public function getCustomerGroupId()
    {
        $groupId = 0;
        $model = $this->_objectManager->create(\Magento\Customer\Model\Group::class);
        $collection  = $model->getCollection();

        $collection->addFieldToFilter("customer_group_code", "General");
        if ($collection->getSize()) {
            foreach ($collection as $group) {
                return $group->getId();
            }
        }

        return $groupId;
    }

    public function getSupplierCustomerGroupId()
    {
        $groupId = 0;
        $model = $this->_objectManager->create(\Magento\Customer\Model\Group::class);
        $collection  = $model->getCollection();

        $collection->addFieldToFilter("customer_group_code", "B2B Suppliers");
        if ($collection->getSize()) {
            foreach ($collection as $group) {
                return $group->getId();
            }
        }

        return $groupId;
    }

    public function getCurrentCustomer()
    {
        return $this->_customerSessionFactory->create()->getCustomer();
    }

    /**
     * Retrieve current supplier customer data
     * @param int $supplierId
     *
     * @return \Magento\Customer\Model\Customer
     */
    public function getCurrentSupplierMageData()
    {
        $sellerId = $this->getSupplierId();
        return $this->getCustomerById($sellerId);
    }

    /**
     * Retrieve customer by id
     * @param int $supplierId
     *
     * @return \Magento\Customer\Model\Customer
     */
    public function getCustomerById($customerId)
    {
        try {
            return $this->_customerFactory->create()->load($customerId);
        } catch (\Exception $e) {
            return null;
        }
    }

    public function getCurrentSupplier()
    {
        $storeId = $this->_storeManager->getStore()->getId();
        $supplierId = $this->getSupplierId();
        $supplier = $this->_sellerFactory->create();

        $collection = $supplier->getCollection();
        $collection->addFieldToFilter('seller_id', $supplierId);
        $collection->addFieldToFilter('store_id', $storeId);

        if ($collection->getSize()) {
            foreach ($collection as $supplier) {
                return $supplier;
            }
        } else {
            $collection = $supplier->getCollection();
            $collection->addFieldToFilter('seller_id', $supplierId);
            if ($collection->getSize()) {
                foreach ($collection as $supplier) {
                    $data = $supplier->getData();
                    unset($data['entity_id']);
                    $data['store_id'] = $storeId;
                    try {
                        $supplier = $this->_sellerFactory->create()->setData($data)->save();
                        return $supplier;
                    } catch (\Exception $e) {
                        $e->getMessage();
                    }
                }
            }
        }

        return $supplier;
    }

    public function getCountryHtmlSelect($defValue = null, $name = 'country_id', $id = 'country', $title = 'Country')
    {
        return $this->_directoryBlock->getCountryHtmlSelect($defValue, $name, $id, $title);
    }

    public function getAllRegions()
    {
        $collection = $this->_objectManager->create(\Magento\Directory\Model\Region::class)
        ->getCollection();
        $result = [];
        foreach ($collection as $item) {
            $result[$item->getCountryId()][$item->getRegionId()] = $item->getName();
        }

        return $result;
    }

    public function createAttribute($attributeCode, $attributeLabel)
    {
        try {
            $customerEntity = $this->_eavConfig->getEntityType('customer');
            $attributeSetId = $customerEntity->getDefaultAttributeSetId();
            $attributeSet = $this->_attributeSetFactory->create();
            $attributeGroupId = $attributeSet->getDefaultGroupId($attributeSetId);

            $attribute = $this->_eavConfig->getAttribute('customer', $attributeCode);
            $attribute->addData(
                [
                    'frontend_type' => 'varchar',
                    'backend_type' => 'varchar',
                    'frontend_label' => $attributeLabel,
                    'frontend_input' => 'text',
                    'sort_order' => 1,
                    'is_visible' => 0,
                    'is_system' => false,
                    'is_user_defined' => false,
                    'position' => 1,
                    'attribute_set_id' => $attributeSetId,
                    'attribute_group_id' => $attributeGroupId,
                    'used_in_forms' => 1,
                ]
            );
            $attribute->save();
        } catch (\Exception $e) {
            $e->getMessage();
        }
    }

    public function createSupplierAttributes()
    {
        $attributes = [];
        $attributes["wk_supplier_company_name"] = "Company Name";
        $attributes["wk_supplier_company_tagline"] = "Company Tagline";
        $attributes["wk_supplier_registered_in"] = "Company Registered In";
        $attributes["wk_supplier_team_size"] = "Company Team Size";
        $attributes["wk_supplier_certification"] = "Company Certification";
        $attributes["wk_supplier_role"] = "Supplier Role";
        $attributes["wk_is_verified_supplier"] = "Verified Supplier";
        $attributes["wk_is_premium_supplier"] = "Premium Supplier";

        $attributes["wk_supplier_response_time"] = "Response Time";
        $attributes["wk_supplier_banner_content"] = "Banner Content";
        $attributes["wk_supplier_response_time_unit"] = "Response Time Unit";

        foreach ($attributes as $attributeCode => $attributeLabel) {
            $this->createAttribute($attributeCode, $attributeLabel);
        }
    }

    public function getCustomer($customerId)
    {
        return $this->_objectManager->create(\Magento\Customer\Model\Customer::class)
                ->load($customerId);
    }

    public function updateOperationalAddress($addressId)
    {
        $address = $this->_objectManager->create(\Magento\Customer\Model\Address::class);
        $address = $address->load($addressId);

        $customer = $this->getCurrentSupplierMageData();
        $firstName = $customer->getFirstname();
        $lastName = $customer->getLastname();

        $country = $this->_request->getParam('operational_country');
        $state = $this->_request->getParam('operational_state');
        $stateId = (int) $this->_request->getParam('operational_state_id');
        $city = $this->_request->getParam('operational_city');
        $addressLine = $this->_request->getParam('operational_address');

        $postcode = $this->_request->getParam('operational_postcode');
        $phone = $this->_request->getParam('operational_phone');

        $address->setCustomerId($customer->getId())
                ->setFirstname($firstName)
                ->setLastname($lastName)
                ->setCountryId($country)
                ->setTelephone($phone)
                ->setPostcode($postcode)
                ->setCity($city)
                ->setStreet($addressLine)
                ->setSaveInAddressBook('0');
        if ($stateId > 0) {
            $address->setRegionId($stateId);
        } else {
            $address->setRegion($state);
        }
        try {
            $address->setId($addressId)->save();
        } catch (\Exception $e) {
            $e->getMessage();
        }

        return $address;
    }

    public function updateCorporateAddress($addressId)
    {
        $address = $this->_objectManager->create(\Magento\Customer\Model\Address::class);
        $address = $address->load($addressId);

        $customer = $this->getCurrentSupplierMageData();
        $firstName = $customer->getFirstname();
        $lastName = $customer->getLastname();

        $country = $this->_request->getParam('corporate_country');
        $state = $this->_request->getParam('corporate_state');
        $stateId = (int) $this->_request->getParam('corporate_state_id');
        $city = $this->_request->getParam('corporate_city');
        $addressLine = $this->_request->getParam('corporate_address');

        $postcode = $this->_request->getParam('corporate_postcode');
        $phone = $this->_request->getParam('corporate_phone');

        $address->setCustomerId($customer->getId())
                ->setFirstname($firstName)
                ->setLastname($lastName)
                ->setCountryId($country)
                ->setTelephone($phone)
                ->setPostcode($postcode)
                ->setCity($city)
                ->setStreet($addressLine)
                ->setSaveInAddressBook('0');
        if ($stateId > 0) {
            $address->setRegionId($stateId);
        } else {
            $address->setRegion($state);
        }
        try {
            $address->setId($addressId)->save();
        } catch (\Exception $e) {
            $e->getMessage();
        }

        return $address;
    }

    public function createCorporateAddress()
    {
        $customer = $this->getCurrentSupplierMageData();
        $firstName = $customer->getFirstname();
        $lastName = $customer->getLastname();

        $country = $this->_request->getParam('corporate_country');
        $state = $this->_request->getParam('corporate_state');
        $stateId = (int) $this->_request->getParam('corporate_state_id');
        $city = $this->_request->getParam('corporate_city');
        $addressLine = $this->_request->getParam('corporate_address');

        $postcode = $this->_request->getParam('corporate_postcode');
        $phone = $this->_request->getParam('corporate_phone');

        $addresss = $this->_objectManager->get(\Magento\Customer\Model\AddressFactory::class);
        $address = $addresss->create();
        $address->setCustomerId($customer->getId())
                ->setFirstname($firstName)
                ->setLastname($lastName)
                ->setCountryId($country)
                ->setTelephone($phone)
                ->setPostcode($postcode)
                ->setCity($city)
                ->setStreet($addressLine)
                ->setSaveInAddressBook('0');
        if ($stateId > 0) {
            $address->setRegionId($stateId);
        } else {
            $address->setRegion($state);
        }
        try {
            $address->save();
        } catch (\Exception $e) {
            $e->getMessage();
        }

        return $address;
    }

    public function createOperationalAddress()
    {
        $customer = $this->getCurrentSupplierMageData();
        $firstName = $customer->getFirstname();
        $lastName = $customer->getLastname();

        $country = $this->_request->getParam('operational_country');
        $state = $this->_request->getParam('operational_state');
        $stateId = $this->_request->getParam('operational_state_id');
        $city = $this->_request->getParam('operational_city');
        $addressLine = $this->_request->getParam('operational_address');

        $postcode = $this->_request->getParam('operational_postcode');
        $phone = $this->_request->getParam('operational_phone');

        $addresss = $this->_objectManager->get(\Magento\Customer\Model\AddressFactory::class);
        $address = $addresss->create();
        $address->setCustomerId($customer->getId())
                ->setFirstname($firstName)
                ->setLastname($lastName)
                ->setCountryId($country)
                ->setTelephone($phone)
                ->setPostcode($postcode)
                ->setCity($city)
                ->setStreet($addressLine)
                ->setSaveInAddressBook('0');
        if ($stateId > 0) {
            $address->setRegionId($stateId);
        } else {
            $address->setRegion($state);
        }
        
        try {
            $address->save();
        } catch (\Exception $e) {
            $e->getMessage();
        }

        return $address;
    }

    public function hasCorporateAddess($supplier)
    {
        if ($supplier->getCorporateAddressId()) {
            return true;
        }

        return false;
    }

    public function hasOperationalAddess($supplier)
    {
        if ($supplier->getOperationalAddressId()) {
            return true;
        }

        return false;
    }

    public function updateCompanyInfo($fields)
    {
        $data = [];
        $supplier = $this->getCurrentSupplier();
        $this->saveCompanyInfo($fields);
        if ($this->hasOperationalAddess($supplier)) {
            $this->updateOperationalAddress($supplier->getOperationalAddressId());
        } else {
            $address = $this->createOperationalAddress();
            $data['operational_address_id'] = $address->getId();
        }

        if ($this->hasCorporateAddess($supplier)) {
            $this->updateCorporateAddress($supplier->getCorporateAddressId());
        } else {
            $address = $this->createCorporateAddress();
            $data['corporate_address_id'] = $address->getId();
        }

        $data['shop_title'] = $this->_request->getParam("wk_supplier_company_name");
        $target = $this->_mediaDirectory->getAbsolutePath('avatar/');

        try {
            /** @var $uploader \Magento\MediaStorage\Model\File\Uploader */
            $uploader = $this->_fileUploaderFactory->create(
                ['fileId' => 'banner_pic']
            );
            $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);
            $uploader->setAllowRenameFiles(true);
            $result = $uploader->save($target);
            if ($result['file']) {
                $data['banner_pic'] = $result['file'];
            }
        } catch (\Exception $e) {
            if ($e->getMessage() != 'The file was not uploaded.') {
                $this->_messageManager->addError($e->getMessage());
            }
        }

        try {
            /** @var $uploaderLogo \Magento\MediaStorage\Model\File\Uploader */
            $uploaderLogo = $this->_fileUploaderFactory->create(
                ['fileId' => 'logo_pic']
            );
            $uploaderLogo->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);
            $uploaderLogo->setAllowRenameFiles(true);
            $resultLogo = $uploaderLogo->save($target);
            if ($resultLogo['file']) {
                $data['logo_pic'] = $resultLogo['file'];
            }
        } catch (\Exception $e) {
            if ($e->getMessage() != 'The file was not uploaded.') {
                $this->_messageManager->addError($e->getMessage());
            }
        }

        if (isset($fields["remove_logo"]) && $fields["remove_logo"]) {
            $data['logo_pic'] = '';
        }

        if (isset($fields["remove_banner"]) && $fields["remove_banner"]) {
            $data['banner_pic'] = '';
        }

        $supplier->addData($data)->setId($supplier->getId())->save();
    }

    public function saveCompanyInfo($fields, $customerId = 0)
    {
        try {
            if ($customerId == 0) {
                $customerId = $this->getSupplierId();
            }
            $customer = $this->_customerRepository->getById($customerId);
            $typeId = $this->_eavEntity->setType('customer')->getTypeId();
            $customData = $fields;

            $savedCustomerData = $customer;
            $customer = $this->_customerDataFactory->create();
            $customData = array_merge(
                $this->_customerMapper->toFlatArray($savedCustomerData),
                $customData
            );
            $customData['id'] = $customerId;

            $this->_dataObjectHelper->populateWithArray(
                $customer,
                $customData,
                \Magento\Customer\Api\Data\CustomerInterface::class
            );
            $this->_customerRepository->save($customer);
        } catch (\Exception $e) {
            $msg = $e->getMessage();
        }
    }

    public function getCompanyName()
    {
        $customer = $this->getCurrentSupplierMageData();
        return $customer->getWkSupplierCompanyName();
    }

    public function getCompanyTagline()
    {
        $customer = $this->getCurrentSupplierMageData();
        return $customer->getWkSupplierCompanyTagline();
    }

    public function getRegisteredIn()
    {
        $customer = $this->getCurrentSupplierMageData();
        return $customer->getWkSupplierRegisteredIn();
    }

    public function getTeamSize()
    {
        $customer = $this->getCurrentSupplierMageData();
        return $customer->getWkSupplierTeamSize();
    }

    public function getCertification()
    {
        $customer = $this->getCurrentSupplierMageData();
        return $customer->getWkSupplierCertification();
    }

    public function getSupplierAddressIds($supplierId = 0)
    {
        $address = [];
        $supplier = $this->getCurrentSupplier();
        $address[] = $supplier->getDefaultAddressId();
        $address[] = $supplier->getOperationalAddressId();
        $address[] = $supplier->getCorporateAddressId();

        return $address;
    }

    public function getCorporateAddress()
    {
        $supplier = $this->getCurrentSupplier();
        $addressId = $supplier->getCorporateAddressId();

        $result = [];
        $result["country"] = "";
        $result["region_id"] = "";
        $result["region"] = "";
        $result["city"] = "";
        $result["postcode"] = "";
        $result["street"] = "";
        $result["telephone"] = "";

        try {
            $address = $this->_objectManager->create(\Magento\Customer\Model\Address::class);
            $address = $address->load($addressId);
            $result["country"] = $address->getCountryId();
            $result["region_id"] = $address->getRegionId();
            $result["region"] = $address->getRegion();
            $result["city"] = $address->getCity();
            $result["postcode"] = $address->getPostcode();
            $result["street"] = implode(", ", $address->getStreet());
            $result["telephone"] = $address->getTelephone();
        } catch (\Exception $e) {
            $error = $e->getMessage();
        }

        return $result;
    }

    public function getOperatioanlAddress()
    {
        $supplier = $this->getCurrentSupplier();
        $addressId = $supplier->getOperationalAddressId();

        $result = [];
        $result["country"] = "";
        $result["region_id"] = "";
        $result["region"] = "";
        $result["city"] = "";
        $result["postcode"] = "";
        $result["street"] = "";
        $result["telephone"] = "";

        try {
            $address = $this->_objectManager->create(\Magento\Customer\Model\Address::class);
            $address = $address->load($addressId);
            $result["country"] = $address->getCountryId();
            $result["region_id"] = $address->getRegionId();
            $result["region"] = $address->getRegion();
            $result["city"] = $address->getCity();
            $result["postcode"] = $address->getPostcode();
            $result["street"] = implode(", ", $address->getStreet());
            $result["telephone"] = $address->getTelephone();
        } catch (\Exception $e) {
            $e->getMessage();
        }

        return $result;
    }

    public function getDefaultAddress()
    {
        $supplier = $this->getCurrentSupplier();
        $addressId = $supplier->getDefaultAddressId();

        $result = [];
        $result["country"] = "";
        $result["region_id"] = "";
        $result["region"] = "";
        $result["city"] = "";
        $result["postcode"] = "";
        $result["street"] = "";
        $result["telephone"] = "";

        try {
            $address = $this->_objectManager->create(\Magento\Customer\Model\Address::class);
            $address = $address->load($addressId);
            $result["country"] = $address->getCountryId();
            $result["region_id"] = $address->getRegionId();
            $result["region"] = $address->getRegion();
            $result["city"] = $address->getCity();
            $result["postcode"] = $address->getPostcode();
            $result["street"] = implode(", ", $address->getStreet());
            $result["telephone"] = $address->getTelephone();
        } catch (\Exception $e) {
            $e->getMessage();
        }

        return $result;
    }

    public function updateSupplierInfo($fields)
    {
        $sellerId = $this->getSupplierId();
        if (!$sellerId) {
            $this->_messageManager->addError(__("Something went wrong."));
            return;
        }
        $typeId = $this->_eavEntity->setType('customer')->getTypeId();
        $customData = $fields;

        $fromTime = (int) $this->_request->getParam('wk_supplier_response_from_time');
        $toTime = (int) $this->_request->getParam('wk_supplier_response_to_time');

        if (array_key_exists("payment_source", $fields)) {
            $this->saveSupplierPaymentInfo($fields, $sellerId);
        }
        if (array_key_exists("min_order_amount", $fields)) {
            $this->saveMinimumAmount($fields, $sellerId);
        }

        if ($toTime > 0) {
            if ($fromTime > $toTime) {
                $toTime = $fromTime + 1;
            }

            $responseTime = $fromTime."-".$toTime;
            $customData['wk_supplier_response_time'] = $responseTime;
        }

        try {
            $savedCustomerData = $this->_customerRepository->getById($sellerId);
            $customer = $this->_customerDataFactory->create();
            $customData = array_merge(
                $this->_customerMapper->toFlatArray($savedCustomerData),
                $customData
            );
            $customData['id'] = $sellerId;
            
            $this->_dataObjectHelper->populateWithArray(
                $customer,
                $customData,
                \Magento\Customer\Api\Data\CustomerInterface::class
            );

            $this->_customerRepository->save($customer);
        } catch (\Exception $e) {
            $e->getMessage();
        }

        $data = [];
        $supplier = $this->getCurrentSupplier();
        $addressId = (int) $supplier->getDefaultAddressId();
        $address = $this->_objectManager->create(\Magento\Customer\Model\Address::class);

        $firstName = $this->_request->getParam('firstname');
        $lastName = $this->_request->getParam('lastname');
        $country = $this->_request->getParam('country_id');
        $state = $this->_request->getParam('state');
        $stateId = $this->_request->getParam('state_id');
        $city = $this->_request->getParam('city');
        $addressLine = $this->_request->getParam('address');
        $postcode = $this->_request->getParam('postcode');
        $phone = $this->_request->getParam('phone');
        if ($addressId > 0) {
            $address = $address->load($addressId);
            $address->setCustomerId($customer->getId())
                    ->setFirstname($firstName)
                    ->setLastname($lastName)
                    ->setCountryId($country)
                    ->setTelephone($phone)
                    ->setCity($city)
                    ->setPostcode($postcode)
                    ->setStreet($addressLine)
                    ->setSaveInAddressBook('0');
            if ($stateId > 0) {
                $address->setRegionId($stateId);
            } else {
                $address->setRegion($state);
            }
            try {
                $address->setId($addressId)->save();
            } catch (\Exception $e) {
                $e->getMessage();
            }
        } else {
            $address->setCustomerId($customer->getId())
                    ->setFirstname($firstName)
                    ->setLastname($lastName)
                    ->setCountryId($country)
                    ->setTelephone($phone)
                    ->setCity($city)
                    ->setPostcode($postcode)
                    ->setStreet($addressLine)
                    ->setSaveInAddressBook('0');
            if ($stateId > 0) {
                $address->setRegionId($stateId);
            } else {
                $address->setRegion($state);
            }

            try {
                $address->save();
                $supplier = $this->_sellerFactory->create();
                $collection = $supplier->getCollection();
                $collection->addFieldToFilter('seller_id', $sellerId);
                $data = ["default_address_id" => $address->getId()];
                if ($collection->getSize()) {
                    foreach ($collection as $supplier) {
                        $supplier->addData($data)->setId($supplier->getId())->save();
                    }
                }
            } catch (\Exception $e) {
                $e->getMessage();
            }
        }
    }

    public function saveSupplierPaymentInfo($fields, $sellerId)
    {
        $collection = $this->_sellerFactory->create()
                            ->getCollection()
                            ->addFieldToFilter('seller_id', $sellerId);

        $autoIds = $collection->getAllIds();
        foreach ($autoIds as $autoId) {
            $value = $this->_sellerFactory->create()->load($autoId);
            $value->setPaymentSource($fields['payment_source']);
            $value->setUpdatedAt($this->_date->gmtDate());
            $value->save();
        }
    }

    public function saveMinimumAmount($fields, $sellerId)
    {
        $salePerPartnerColl = $this->mpSalesPartner->create()
                                    ->getCollection()
                                    ->addFieldToFilter(
                                        'seller_id',
                                        $sellerId
                                    );
        if ($salePerPartnerColl->getSize() == 1) {
            foreach ($salePerPartnerColl as $verifyrow) {
                $verifyrow->setMinOrderAmount($fields['min_order_amount']);
                $verifyrow->setMinOrderStatus(1);
                $verifyrow->save();
            }
        } else {
            $collectioninsert = $this->mpSalesPartner->create();
            $collectioninsert->setSellerId($sellerId);
            $collectioninsert->setMinOrderAmount($fields['min_order_amount']);
            $collectioninsert->setMinOrderStatus(1);
            $collectioninsert->save();
        }
    }

    // public function getWysiwygUrl() {
    //     $currentTreePath = $this->wysiwygImages->idEncode(
    //         \Magento\Cms\Model\Wysiwyg\Config::IMAGE_DIRECTORY
    //     );
    //     $url =  $this->getUrl(
    //         'marketplace/wysiwyg_images/index',
    //         [
    //             'current_tree_path' => $currentTreePath
    //         ]
    //     );
    //     return $url;
    // }

    public function updateCompanyData($fields)
    {
        $sellerId = $this->getSupplierId();
        $supplier = $this->getCurrentSupplier();
        $supplier->addData($fields)->setId($supplier->getId())->save();

        /**
         * set taxvat number for seller
         */
        if ($fields['taxvat']) {
            try {
                $customer = $this->_customerRepository->getById($sellerId);
                $customer->setTaxvat($fields['taxvat']);
                $customer->setId($sellerId);
                $this->_customerRepository->save($customer);
            } catch (\Exception $e) {
                $e->getMessage();
            }
        }
    }

    public function updatePolicies($fields)
    {
        $supplier = $this->getCurrentSupplier();
        $supplier->addData($fields)->setId($supplier->getId())->save();
    }

    public function getResponseRate($sellerId = 0, $responseTime = 0, $responseTimeUnit = 'hour')
    {
        try {
            if ($responseTime) {
                $responseTimeArr = explode('-', $responseTime);
                if (isset($responseTimeArr[1])) {
                    $responseTime = $responseTimeArr[1];
                }
            }
            $responseTime = (int)$responseTime;

            $collection = $this->_messageFactory->create()
                ->getCollection()
                ->addFieldToFilter("sender_id", $sellerId);
            $collection->getSelect()->where(
                "DATE_ADD(`created_at`, INTERVAL ".$responseTime." ".$responseTimeUnit.") <= NOW()"
            );
            if (!count($collection)) {
                return '100';
            }
            $customerResFlagCount = $collection->getCustomerResponseRateCalFlagCount();
            $supplierResFlagCount = $collection->getSupplierResponseRateCalFlagCount();
            if (isset($supplierResFlagCount[0]) && isset($customerResFlagCount[0])) {
                $rate = round($supplierResFlagCount[0]/$customerResFlagCount[0] * 100);
                if ($rate > 100) {
                    $rate = 100;
                }
                if ($rate < 0) {
                    $rate = 0;
                }
                return $rate;
            } else {
                return '100';
            }

        } catch (\Exception $e) {
            return 0;
        }
    }

    public function getSupplierInfoById($supplierId)
    {
        try {
            return $this->_customerFactory->create()->load($supplierId);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return null;
        }
    }

    /**
     * @param int $addressId
     * @return \Magento\Customer\Model\Address
     */
    public function getAddressById($addressId)
    {
        try {
            return $this->_objectManager->create(\Magento\Customer\Model\Address::class)->load($addressId);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return null;
        }
    }

    /**
     * @param string $countryCode
     * @return string
     */
    public function getCountryName($countryCode)
    {
        try {
            $name = $this->_objectManager->create(\Magento\Directory\Model\Country::class)->loadByCode(
                $countryCode
            )->getName();
        } catch (\Exception $e) {
            $name = null;
        }
        return $name;
    }

    /**
     * @param int $addressId
     * @return \Magento\Customer\Model\Address
     */
    public function getRepoAddressById($addressId)
    {
        try {
            return $this->_objectManager->create(
                \Magento\Customer\Api\AddressRepositoryInterface::class
            )->getById($addressId);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return null;
        }
    }

    /**
     * Render an address as HTML and return the result
     *
     * @param \Magento\Customer\Model\Address
     * @return string
     */
    public function getAddressHtml($address)
    {
        if ($address !== null) {
            /** @var \Magento\Customer\Block\Address\Renderer\RendererInterface $renderer */
            $renderer = $this->_objectManager->create(
                \Magento\Customer\Model\Address\Config::class
            )->getFormatByCode('html')->getRenderer();
            return $renderer->renderArray(
                $this->_objectManager->create(
                    \Magento\Customer\Model\Address\Mapper::class
                )->toFlatArray($address)
            );
        }
        return '';
    }

    public function getVerifiedSupplierStatusHtml()
    {
        if ($this->isVerifiedSupplier()) {
            return "<div class='wk-b2b-supplier-label-verified wk-b2b-supplier-label'>Verified<div>";
        }

        return "<div class='wk-b2b-supplier-label-not-verified wk-b2b-supplier-label'>Not Verified<div>";
    }

    public function getVerifiedSupplierInfoHtml()
    {
        if (!$this->isVerifiedSupplier()) {
            $customer = $this->getCurrentSupplierMageData();
            $email = "<span>".$customer->getEmail()."</span>";
            $link = "<a href='".$this->_storeManager->getStore()
            ->getUrl('b2bmarketplace/supplier/verification_send')."'>click here</a>";
            $html = "";
            $html .= "<div class='wk-b2b-supplier-verification-bar'>";
            $html .= __("A verification email is sent to %1 , 
            check your inbox for verification email or %2 to resend link.", $email, $link);
            $html .= "</div>";
            return $html;
        }
        return "";
    }

    public function validateToken($token)
    {
        $storeId = $this->_storeManager->getStore()->getId();
        $result = [];
        try {
            $collection = $this->_sellerFactory->create()->getCollection();
            $collection->addFieldToFilter("token", $token);

            if ($collection->getSize()) {
                foreach ($collection as $item) {
                    $customerId = $item->getSellerId();
                    $customer = $this->getCustomer($customerId);
                    $customerData = ["wk_is_verified_supplier" => 1];
                    $this->saveCustomerData($customer, $customerData);
                    $result["error"] = false;
                    $result["msg"] = "Verified";
                    $item->setToken("")->setId($item->getId())->save();
                }
                $this->_messageManager->addSuccess(__("Email Verified"));
            } else {
                $this->_messageManager->addError(__("Link expired."));
            }
        } catch (\Exception $e) {
            $e->getMessage();
        }
        return $result;
    }

    public function saveCustomerData($customer, $customerData)
    {
        try {
            $customerId = $customer->getId();
            $typeId = $this->_eavEntity->setType('customer')->getTypeId();
            $savedCustomerData = $this->_customerRepository->getById($customerId);
            $customer = $this->_customerDataFactory->create();
            $customerData = array_merge(
                $this->_customerMapper->toFlatArray($savedCustomerData),
                $customerData
            );
            $customerData['id'] = $customerId;

            $this->_dataObjectHelper->populateWithArray(
                $customer,
                $customerData,
                \Magento\Customer\Api\Data\CustomerInterface::class
            );
            $this->_customerRepository->save($customer);
        } catch (\Exception $e) {
            $msg = $e->getMessage();
        }
    }

    public function getAdminName()
    {
        return "Admin";
    }

    public function getAdminEmail()
    {
        return $this->_mpHelper->getAdminEmailId();
    }

    public function sendVerifySupplierEmail($name, $email, $token)
    {
        $url = $this->_storeManager->getStore()->getUrl('b2bmarketplace/supplier/verification_verify');
        $url .= "token/".$token;

        $templateVars = [];
        $templateVars["name"] = $name;
        $templateVars["url"] = $url;

        $senderDetails = [];
        $senderDetails["name"] = $this->getAdminName();
        $senderDetails["email"] = $this->getAdminEmail();

        $receiverDetails = [];
        $receiverDetails["name"] = $name;
        $receiverDetails["email"] = $email;

        $this->_objectManager->create(
            'Webkul\B2BMarketplace\Helper\Email'
        )->verifiedSupplierEmail($templateVars, $senderDetails, $receiverDetails);
    }

    public function processVerifySupplier()
    {
        $customer = $this->getCurrentSupplierMageData();
        $email = $customer->getEmail();
        $token = $this->getToken();
        $data["token"] = $token;
        $name = $customer->getFirstname()." ".$customer->getLastname();

        $supplierId = $this->getSupplierId();
        $supplier = $this->_sellerFactory->create();

        $collection = $supplier->getCollection();
        $collection->addFieldToFilter('seller_id', $supplierId);
        $emailSent = false;
        if ($collection->getSize()) {
            foreach ($collection as $supplier) {
                $supplier->addData(["token" => $token])->setId($supplier->getId())->save();
                $emailSent = true;
            }
        }

        if ($emailSent) {
            $this->sendVerifySupplierEmail($name, $email, $token);
        }
    }

    public function isVerifiedSupplier($sellerId = 0, $isLoggedIn = true)
    {
        if ($isLoggedIn) {
            return $this->getCurrentSupplierMageData()->getWkIsVerifiedSupplier();
        } else {
            $customer = $this->getCustomer($sellerId);
            return $this->getCurrentSupplierMageData()->getWkIsVerifiedSupplier();
        }
    }

    public function getToken()
    {
        $letters = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $token = "";
        $len = strlen($letters);
        for ($i = 0; $i < 48; $i++) {
            $key = rand(0, $len-1);
            $token .= $letters[$key];
        }

        return $token;
    }

    public function getViewFileUrl($fileId, array $params = [])
    {
        $this->_assetRepo = $this->_objectManager->create(\Magento\Framework\View\Asset\Repository::class);
        $params = array_merge(['_secure' => $this->_request->isSecure()], $params);
        return $this->_assetRepo->getUrlWithParams($fileId, $params);
    }

    public function isLoggedInSupplier()
    {
        if ($customer = $this->getCurrentSupplierMageData()) {
            if ($this->getSupplierCustomerGroupId() > 0) {
                if ($this->getSupplierCustomerGroupId() == $customer->getGroupId()) {
                    return true;
                }
            }
        }

        return false;
    }

    public function isCustomerPages()
    {
        $allowedActions = ["section"];
        $allowedController = ["section"];
        $controller = $this->_request->getControllerName();
        $action = $this->_request->getActionName();
        $module = $this->_request->getModuleName();
        
        if ($module == "customer") {
            if (in_array($controller, $allowedController)) {
                return false;
            }

            return true;
        }
        
        return false;
    }

    /**
     * Get Full Action Name
     *
     * @return string
     */
    public function getFullActionName()
    {
        return $this->_request->getFullActionName();
    }

    public function getSupplResponseTimeDetails()
    {
        $result = [];
        $customer = $this->getCurrentSupplierMageData();
        $details = $customer->getWkSupplierResponseTime();
        $to = '';
        $from = '';
        
        if (strpos((string)$details, "-") !== false) {
            list($from, $to) = explode("-", $details);
        }
        
        $result["from"] = $from;
        $result["to"] = $to;

        return $result;
    }

    public function getUnits()
    {
        $result = [];
        $result["hour"] = __("Hours");
        $result["day"] = __("Days");

        return $result;
    }

    public function isLoggedInBuyer()
    {
        if ($customer = $this->getCurrentCustomer()) {
            if ($this->getSupplierCustomerGroupId() == $customer->getGroupId()) {
                return false;
            }

            return true;
        }

        return false;
    }

    public function isSupplierPages()
    {
        $actions = ["becomeseller"];
        $controllers = ["account"];
        $controller = $this->_request->getControllerName();
        $action = $this->_request->getActionName();
        $module = $this->_request->getModuleName();
        
        if ($module == "marketplace") {
            if (in_array($action, $actions)) {
                return true;
            }
        }

        $actions = ["message", "verification", "settings"];
        $controllers = ["supplier"];
        if ($module == "b2bmarketplace") {
            if (in_array($controller, $controllers)) {
                if (in_array($action, $actions)) {
                    return true;
                }
            }
        }

        $actions = ["managequote"];
        $controllers = ["sellerquote"];
        if ($module == "mpquotesystem") {
            if (in_array($controller, $controllers)) {
                if (in_array($action, $actions)) {
                    return true;
                }
            }
        }

        return false;
    }

    public function isMpLoginPage()
    {
        $controller = $this->_request->getControllerName();
        $action = $this->_request->getActionName();
        $module = $this->_request->getModuleName();
        if ($module == "marketplace") {
            if ($controller == "account" && $action == "login") {
                return true;
            }
        }

        return false;
    }

    public function getSupplierLoginUrl()
    {
        return $this->_storeManager->getStore()->getUrl('b2bmarketplace/supplier/login');
    }

    public function isSupplierLoginRequest()
    {
        if ($this->_request->getParam("supplier_login")) {
            return true;
        }

        return false;
    }

    public function isSupplierEmail($email)
    {
        $groupId = $this->getSupplierCustomerGroupId();
        $collection = $this->_customerCollectionFactory->create();
        $tableName = $this->_resource->getTableName('marketplace_userdata');
        $sql = "e.entity_id = seller.seller_id ";
        $collection->getSelect()->join(['seller' => $tableName], $sql, '*');
        $sql = "e.email = '".$email."'";
        $sql .= " and e.group_id = $groupId";
        $collection->getSelect()->where($sql);
        if ($collection->getSize()) {
            return true;
        }

        return false;
    }

    public function getDefaultLogo()
    {
        $image = 'noimage.png';
        return $this->_mpHelper->getMediaUrl().'avatar/'.$image;
    }

    public function getSellerLogo()
    {
        $supplier = $this->getCurrentSupplier();
        $logo = $supplier->getLogoPic();
        if ($logo != "") {
            return $this->_mpHelper->getMediaUrl().'avatar/'.$logo;
        }

        return $this->getDefaultLogo();
    }

    public function getDefaultBanner()
    {
        $image = 'banner-image.png';
        return $this->_mpHelper->getMediaUrl().'avatar/'.$image;
    }

    public function getSellerBanner()
    {
        $supplier = $this->getCurrentSupplier();
        $banner = $supplier->getBannerPic();
        if ($banner != "") {
            return $this->_mpHelper->getMediaUrl().'avatar/'.$banner;
        }

        return $this->getDefaultBanner();
    }

    public function getSupplierCategories()
    {
        $result = [];
        $supplierId = $this->getSupplierId();
        $collection = $this->b2bCategoryCollectionFactory->create();
        $collection->addFieldToFilter("supplier_id", $supplierId);
        foreach ($collection as $category) {
            try {
                $id = $category->getCategoryId();
                $result[$id] = $this->categoryRepository->get($id)->getName();
            } catch (\Exception $e) {
                $category->delete();
            }
        }

        return $result;
    }

    public function updateCategories()
    {
        $data = [];
        $supplierId = $this->getSupplierId();
        $categories = $this->_request->getParam("category", []);
        
        $collection = $this->b2bCategoryCollectionFactory->create();
        $collection->addFieldToFilter("supplier_id", $supplierId);
        foreach ($collection as $item) {
            $item->delete();
        }

        foreach ($categories as $id => $value) {
            $data[] = ["supplier_id" => $supplierId, "category_id" => $id];
        }

        try {
            $connection = $this->_objectManager
            ->create(\Magento\Framework\Module\ModuleResource::class)->getConnection();
            $connection->beginTransaction();
            $connection->insertMultiple($this->_resource->getTableName('wk_b2b_supplier_categories'), $data);
            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollBack();
        }
    }

    public function forwardQuoteTime()
    {
        return $this->scopeConfig->getValue(
            'marketplace/quote_settings/forward_quote_time',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get config value.
     *
     * @param string $path
     * @return string|null
     */
    public function getConfig($path)
    {
        return $this->scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * getMediaUrl.
     *
     * @return string|null
     */
    public function getMediaUrl()
    {
        return $this->_storeManager->getStore()->getBaseUrl(
            \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
        );
    }

    /**
     * Retrieve formatting date
     *
     * @param null|string|\DateTimeInterface $date
     * @param int $format
     * @param bool $showTime
     * @param null|string $timezone
     * @return string
     */
    public function formatDate(
        $date = null,
        $format = \IntlDateFormatter::SHORT,
        $showTime = false,
        $timezone = null
    ) {
        try {
            $date = $date instanceof \DateTimeInterface ? $date : new \DateTime($date);
            return $this->timezone->formatDateTime(
                $date,
                $format,
                $showTime ? \IntlDateFormatter::SHORT : \IntlDateFormatter::NONE,
                null,
                $timezone
            );
        } catch (\Exception $e) {
            $this->logger->info($e->getMessage());
            return $date;
        }
    }

    /**
     * Get Default Welcome Message
     *
     * @return string|null
     */
    public function getDefaultWelcomeMessage()
    {
        $path = 'design/header/welcome';
        return $this->scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getSupplierRegistrationUrl()
    {
        $url = $this->_urlBuilder->getUrl(
            'b2bmarketplace/supplier/create',
            [
                '_secure' => $this->_request->isSecure(),
            ]
        );

        return $url;
    }
}
