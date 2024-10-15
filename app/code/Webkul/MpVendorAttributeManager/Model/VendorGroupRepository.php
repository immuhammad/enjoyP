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

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Webkul\MpVendorAttributeManager\Api\Data\VendorGroupSearchResultsInterfaceFactory;

/**
 * Webkul MpVendorAttributeManager VendorGroupRepository Class
 */
class VendorGroupRepository implements \Webkul\MpVendorAttributeManager\Api\VendorGroupRepositoryInterface
{
    /**
     * @var \Webkul\MpVendorAttributeManager\Model\VendorGroupFactory
     */
    protected $modelFactory = null;

    /**
     * @var \Webkul\MpVendorAttributeManager\Model\ResourceModel\VendorGroup\CollectionFactory
     */
    protected $collectionFactory = null;

    /**
     * Constructor
     *
     * @param \Webkul\MpVendorAttributeManager\Model\VendorGroupFactory $modelFactory
     * @param \Webkul\MpVendorAttributeManager\Model\ResourceModel\VendorGroup\CollectionFactory $collectionFactory
     * @param VendorGroupSearchResultsInterfaceFactory $searchResultFactory
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        \Webkul\MpVendorAttributeManager\Model\VendorGroupFactory $modelFactory,
        \Webkul\MpVendorAttributeManager\Model\ResourceModel\VendorGroup\CollectionFactory $collectionFactory,
        VendorGroupSearchResultsInterfaceFactory $searchResultFactory,
        CollectionProcessorInterface $collectionProcessor
    ) {
        $this->modelFactory = $modelFactory;
        $this->collectionFactory = $collectionFactory;
        $this->searchResultFactory = $searchResultFactory;
        $this->collectionProcessor = $collectionProcessor;
    }

    /**
     * Function get by id
     *
     * @param int $id
     * @return \Webkul\MpVendorAttributeManager\Model\VendorGroup
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
     * @param \Webkul\MpVendorAttributeManager\Model\VendorGroup $subject
     * @return \Webkul\MpVendorAttributeManager\Model\VendorGroup
     */
    public function save(\Webkul\MpVendorAttributeManager\Model\VendorGroup $subject)
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
     * @param SearchCriteriaInterface $criteria
     * @return VendorGroupSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $criteria)
    {
        $collection = $this->collectionFactory->create();

        $this->collectionProcessor->process($criteria, $collection);

        $collection->load();

        $searchResults = $this->searchResultFactory->create();
        $searchResults->setSearchCriteria($criteria);
        $searchResults->setItems($collection->getData());
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * Function delete
     *
     * @param \Webkul\MpVendorAttributeManager\Model\VendorGroup $subject
     * @return boolean
     */
    public function delete(\Webkul\MpVendorAttributeManager\Model\VendorGroup $subject)
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
