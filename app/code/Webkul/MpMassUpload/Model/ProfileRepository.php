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
use Webkul\MpMassUpload\Api\Data\ProfileInterface;
use Webkul\MpMassUpload\Model\ResourceModel\Profile\CollectionFactory;
use Webkul\MpMassUpload\Model\ResourceModel\Profile as ResourceModelProfile;

class ProfileRepository implements \Webkul\MpMassUpload\Api\ProfileRepositoryInterface
{
    /**
     * @var ProfileFactory
     */
    protected $_profileFactory;

    /**
     * @var Profile[]
     */
    protected $_instancesById = [];

    /**
     * @var CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @var ResourceModelProfile
     */
    protected $_resourceModel;

    /**
     * @param ProfileFactory       $profileFactory
     * @param CollectionFactory    $collectionFactory
     * @param ResourceModelProfile $resourceModel
     */
    public function __construct(
        ProfileFactory $profileFactory,
        CollectionFactory $collectionFactory,
        ResourceModelProfile $resourceModel
    ) {
        $this->_profileFactory = $profileFactory;
        $this->_collectionFactory = $collectionFactory;
        $this->_resourceModel = $resourceModel;
    }

    /**
     * get profile data by entity id
     *
     * @param int $entityId
     * @return void
     */
    public function get($entityId)
    {
        $profileData = $this->_profileFactory->create();
        /** @var \Webkul\MpMassUpload\Model\ResourceModel\Profile\Collection $profileData */
        $profileData->load($entityId);
        if (!$profileData->getId()) {
            $this->_instancesById[$entityId] = $profileData;
        }
        $this->_instancesById[$entityId] = $profileData;

        return $this->_instancesById[$entityId];
    }

    /**
     * get profile data by seller id
     *
     * @param int $sellerId
     * @return collection
     */
    public function getBySellerId($sellerId)
    {
        $profileCollection = $this->_collectionFactory->create()
                ->addFieldToFilter('customer_id', $sellerId);
        $profileCollection->load();

        return $profileCollection;
    }

    /**
     * get attribute profile data by profile name
     *
     * @param int $sellerId
     * @param string $profileName
     * @return collection
     */
    public function getByProfileName($sellerId, $profileName)
    {
        $profileCollection = $this->_collectionFactory->create()
                ->addFieldToFilter('customer_id', $sellerId)
                ->addFieldToFilter('profile_name', $profileId);
        $profileCollection->load();

        return $profileCollection;
    }

    /**
     * getList
     *
     * @return collection
     */
    public function getList()
    {
        /** @var \Webkul\MpMassUpload\Model\ResourceModel\Profile\Collection $collection */
        $collection = $this->_collectionFactory->create();
        $collection->load();

        return $collection;
    }

    /**
     * delete Profile
     *
     * @param ProfileInterface $profile
     * @return booloean
     */
    public function delete(ProfileInterface $profile)
    {
        $entityId = $profile->getId();
        try {
            $this->_resourceModel->delete($profile);
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\StateException(
                __('Unable to remove temp profile data record with id %1', $entityId)
            );
        }
        unset($this->_instancesById[$entityId]);

        return true;
    }

    /**
     * delete Profile by id
     *
     * @param int $entityId
     * @return void
     */
    public function deleteById($entityId)
    {
        $profile = $this->get($entityId);

        return $this->delete($profile);
    }

    /**
     * delete Profile by seller id
     *
     * @param int $sellerId
     * @return void
     */
    public function deleteBySellerId($sellerId)
    {
        $profile = $this->getBySellerId($sellerId);

        return $this->delete($profile);
    }

    /**
     * delete Profile by profile name
     *
     * @param int $sellerId
     * @param string $profileName
     * @return void
     */
    public function deleteByProfileName($sellerId, $profileName)
    {
        $profile = $this->getByProfileName($sellerId, $profileName);

        return $this->delete($profile);
    }
}
