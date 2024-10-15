<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpMassUpload
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpMassUpload\Model;

use Magento\Framework\Exception\NoSuchEntityException;
use Webkul\MpMassUpload\Api\Data\AttributeMappingInterface;
use Webkul\MpMassUpload\Model\ResourceModel\AttributeMapping\CollectionFactory;
use Webkul\MpMassUpload\Model\ResourceModel\AttributeMapping as ResourceModelAttributeMapping;

class AttributeMappingRepository implements \Webkul\MpMassUpload\Api\AttributeMappingRepositoryInterface
{
    /**
     * @var AttributeMappingFactory
     */
    protected $_attributeMappingFactory;

    /**
     * @var AttributeMapping[]
     */
    protected $_instancesById = [];

    /**
     * @var CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @var ResourceModelAttributeMapping
     */
    protected $_resourceModel;

    /**
     * @param AttributeMappingFactory       $attributeMappingFactory
     * @param CollectionFactory             $collectionFactory
     * @param ResourceModelAttributeMapping $resourceModel
     */
    public function __construct(
        AttributeMappingFactory $attributeMappingFactory,
        CollectionFactory $collectionFactory,
        ResourceModelAttributeMapping $resourceModel
    ) {
        $this->_attributeMappingFactory = $attributeMappingFactory;
        $this->_collectionFactory = $collectionFactory;
        $this->_resourceModel = $resourceModel;
    }

    /**
     * get attribute mapping data by entity id
     *
     * @param int $entityId
     * @return collection
     */
    public function get($entityId)
    {
        $attributeMappingData = $this->_attributeMappingFactory->create();
        /** @var \Webkul\MpMassUpload\Model\ResourceModel\AttributeMapping\Collection $attributeMappingData */
        $attributeMappingData->load($entityId);
        if (!$attributeMappingData->getId()) {
            $this->_instancesById[$entityId] = $attributeMappingData;
        }
        $this->_instancesById[$entityId] = $attributeMappingData;

        return $this->_instancesById[$entityId];
    }

    /**
     * get attribute mapping data by profile id
     *
     * @param int $profileId
     * @return collection
     */
    public function getByProfileId($profileId)
    {
        $attributeMappingCollection = $this->_collectionFactory->create()
                ->addFieldToFilter('profile_id', $profileId);
        $attributeMappingCollection->load();

        return $attributeMappingCollection;
    }

    /**
     * get attribute mapping By File Attribute
     *
     * @param int $profileId
     * @param string $fileAttribute
     * @return collection
     */
    public function getByFileAttribute($profileId, $fileAttribute)
    {
        $attributeMappingCollection = $this->_collectionFactory->create()
                ->addFieldToFilter('profile_id', $profileId)
                ->addFieldToFilter('file_attribute', $fileAttribute);
        $attributeMappingCollection->load();

        return $attributeMappingCollection;
    }

    /**
     * get attribute mapping By Magento Attribute
     *
     * @param int $profileId
     * @param string $mageAttribute
     * @return collection
     */
    public function getByMageAttribute($profileId, $mageAttribute)
    {
        $attributeMappingCollection = $this->_collectionFactory->create()
                ->addFieldToFilter('profile_id', $profileId)
                ->addFieldToFilter('mage_attribute', $mageAttribute);
        $attributeMappingCollection->load();

        return $attributeMappingCollection;
    }

    /**
     * getList
     *
     * @return collection
     */
    public function getList()
    {
        /** @var \Webkul\MpMassUpload\Model\ResourceModel\AttributeMapping\Collection $collection */
        $collection = $this->_collectionFactory->create();
        $collection->load();

        return $collection;
    }

    /**
     * Delete Attribute Mapping
     *
     * @param AttributeMappingInterface $attributeMapping
     * @return boolean
     */
    public function delete(AttributeMappingInterface $attributeMapping)
    {
        $entityId = $attributeMapping->getId();
        try {
            $this->_resourceModel->delete($attributeMapping);
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\StateException(
                __('Unable to remove attribute mapped data record with id %1', $entityId)
            );
        }
        unset($this->_instancesById[$entityId]);

        return true;
    }

    /**
     * Delete Attribute Mapping By Id
     *
     * @param int $entityId
     * @return void
     */
    public function deleteById($entityId)
    {
        $attributeMapping = $this->get($entityId);

        return $this->delete($attributeMapping);
    }

    /**
     * Delete Attribute Mapping By Profile Id
     *
     * @param int $profileId
     * @return void
     */
    public function deleteByProfileId($profileId)
    {
        $attributeMapping = $this->getByProfileId($profileId);

        return $this->delete($attributeMapping);
    }
}
