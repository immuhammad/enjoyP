<?php
/**
 * Webkul Software.
 *
 * @category Webkul
 * @package Webkul_MpVendorAttributeManager
 * @author Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */
namespace Webkul\MpVendorAttributeManager\Model;

use \Webkul\MpVendorAttributeManager\Model\ResourceModel\VendorAssignGroup\CollectionFactory;

/**
 * Webkul MpVendorAttributeManager VendorAssignGroupRepository Class
 */
class VendorAssignGroupRepository implements \Webkul\MpVendorAttributeManager\Api\VendorAssignGroupRepositoryInterface
{
    /**
     * @var \Webkul\MpVendorAttributeManager\Model\VendorAssignGroupFactory
     */
    protected $modelFactory = null;

    /**
     * @var \Webkul\MpVendorAttributeManager\Model\ResourceModel\VendorAssignGroup\CollectionFactory
     */
    protected $collectionFactory = null;

    /**
     * Constructor
     *
     * @param \Webkul\MpVendorAttributeManager\Model\VendorAssignGroupFactory $modelFactory
     * @param CollectionFactory $collectionFactory
     * @return void
     */
    public function __construct(
        \Webkul\MpVendorAttributeManager\Model\VendorAssignGroupFactory $modelFactory,
        \Webkul\MpVendorAttributeManager\Model\ResourceModel\VendorAssignGroup\CollectionFactory $collectionFactory
    ) {
        $this->modelFactory = $modelFactory;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Function get by id
     *
     * @param int $id
     * @return \Webkul\MpVendorAttributeManager\Model\VendorAssignGroup
     */
    public function getById($id)
    {
        $model = $this->modelFactory->create()->load($id);
        if (!$model->getId()) {
            throw new \Magento\Framework\Exception\NoSuchEntityException(
                __('The model with the "%1" ID doesn\'t exist.', $id)
            );
        }
        return $model;
    }

    /**
     * Function save
     *
     * @param \Webkul\MpVendorAttributeManager\Model\VendorAssignGroup $subject
     * @return \Webkul\MpVendorAttributeManager\Model\VendorAssignGroup
     */
    public function save(\Webkul\MpVendorAttributeManager\Model\VendorAssignGroup $subject)
    {
        try {
            $subject->save();
        } catch (\Exception $exception) {
            throw new \Magento\Framework\Exception\CouldNotSaveException(
                __($exception->getMessage())
            );
        }
        return $subject;
    }

    /**
     * Function get list
     *
     * @param Magento\Framework\Api\SearchCriteriaInterface $criteria
     * @return Magento\Framework\Api\SearchResults
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $criteria)
    {
        $collection = $this->collectionFactory->create();
        return $collection;
    }

    /**
     * Function delete
     *
     * @param \Webkul\MpVendorAttributeManager\Model\VendorAssignGroup $subject
     * @return boolean
     */
    public function delete(\Webkul\MpVendorAttributeManager\Model\VendorAssignGroup $subject)
    {
        try {
            $subject->delete();
        } catch (\Exception $exception) {
            throw new \Magento\Framework\Exception\CouldNotDeleteException(
                __($exception->getMessage())
            );
        }
        return true;
    }

    /**
     * Function delete by id
     *
     * @param int $id
     * @return boolean
     */
    public function deleteById($id)
    {
        return $this->delete($this->getById($id));
    }
}
