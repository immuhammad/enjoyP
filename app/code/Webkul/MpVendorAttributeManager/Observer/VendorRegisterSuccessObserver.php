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
namespace Webkul\MpVendorAttributeManager\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Eav\Model\Entity;
use Magento\Customer\Model\ResourceModel\Attribute\CollectionFactory;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Customer\Model\Customer\Mapper;
use Magento\Framework\Api\DataObjectHelper;

class VendorRegisterSuccessObserver implements ObserverInterface
{
    /**
     * @var \Magento\Eav\Model\Entity
     */
    protected $_eavEntity;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Attribute\CollectionFactory
     */
    protected $_attributeCollectionFactory;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $_customerRepository;

    /**
     * @var \Magento\Customer\Api\Data\CustomerInterfaceFactory
     */
    protected $_customerDataFactory;

    /**
     * @var \Magento\Customer\Model\Customer\Mapper
     */
    protected $_customerMapper;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    protected $_dataObjectHelper;

    /**
     * @param Entity $eavEntity
     * @param CollectionFactory $attributeCollectionFactory
     * @param CustomerRepositoryInterface $customerRepository
     * @param CustomerInterfaceFactory $customerDataFactory
     * @param Mapper $customerMapper
     * @param DataObjectHelper $dataObjectHelper
     */
    public function __construct(
        Entity $eavEntity,
        CollectionFactory $attributeCollectionFactory,
        CustomerRepositoryInterface $customerRepository,
        CustomerInterfaceFactory $customerDataFactory,
        Mapper $customerMapper,
        DataObjectHelper $dataObjectHelper
    ) {
        $this->_eavEntity = $eavEntity;
        $this->_attributeCollectionFactory = $attributeCollectionFactory;
        $this->_customerRepository = $customerRepository;
        $this->_customerDataFactory = $customerDataFactory;
        $this->_customerMapper = $customerMapper;
        $this->_dataObjectHelper = $dataObjectHelper;
    }

    /**
     * Customer register event handler.
     *
     * Save vendor attributes
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $data = $observer['account_controller'];
        $paramData = $data->getRequest();
        $customer = $observer->getCustomer();
        $customerId = $customer->getId();
        $typeId = $this->_eavEntity->setType('customer')->getTypeId();
        $collection = $this->_attributeCollectionFactory->create()
                        ->setEntityTypeFilter($typeId)
                        ->addVisibleFilter()
                        ->setOrder('sort_order', 'ASC');
        $customData = $paramData->getPostValue();

        $savedCustomerData = $this->_customerRepository->getById($customerId);
        $customer = $this->_customerDataFactory->create();
        $customData = array_merge(
            $customData,
            $this->_customerMapper->toFlatArray($savedCustomerData)
        );
        $customData['id'] = $customerId;
        if (!isset($customData['is_vendor_group'])) {
            $customData['is_vendor_group'] = 0;
        }
        $this->_dataObjectHelper->populateWithArray(
            $customer,
            $customData,
            \Magento\Customer\Api\Data\CustomerInterface::class
        );
        $this->_customerRepository->save($customer);
    }
}
