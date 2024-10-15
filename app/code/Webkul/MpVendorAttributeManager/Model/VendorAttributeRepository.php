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
use Webkul\MpVendorAttributeManager\Api\Data\VendorAttributeSearchResultsInterfaceFactory;

/**
 * Webkul MpVendorAttributeManager VendorAttributeRepository Class
 */
class VendorAttributeRepository implements \Webkul\MpVendorAttributeManager\Api\VendorAttributeRepositoryInterface
{
    /**
     * @var \Webkul\MpVendorAttributeManager\Model\VendorAttributeFactory
     */
    protected $modelFactory = null;

    /**
     * @var \Webkul\MpVendorAttributeManager\Model\ResourceModel\VendorAttribute\CollectionFactory
     */
    protected $collectionFactory = null;

    /**
     * Constructor
     *
     * @param \Webkul\MpVendorAttributeManager\Model\VendorAttributeFactory $modelFactory
     * @param \Webkul\MpVendorAttributeManager\Model\ResourceModel\VendorAttribute\CollectionFactory $collectionFactory
     * @param VendorAttributeSearchResultsInterfaceFactory $searchResultFactory
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        \Webkul\MpVendorAttributeManager\Model\VendorAttributeFactory $modelFactory,
        \Webkul\MpVendorAttributeManager\Model\ResourceModel\VendorAttribute\CollectionFactory $collectionFactory,
        VendorAttributeSearchResultsInterfaceFactory $searchResultFactory,
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
     * @return \Webkul\MpVendorAttributeManager\Model\VendorAttribute
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
     * @param \Webkul\MpVendorAttributeManager\Model\VendorAttribute $subject
     * @return \Webkul\MpVendorAttributeManager\Model\VendorAttribute
     */
    public function save(\Webkul\MpVendorAttributeManager\Model\VendorAttribute $subject)
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
     * @return VendorAttributeSearchResultsInterface
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
     * Function get joined list with eav_attibute
     *
     * @param SearchCriteriaInterface $criteria
     * @return VendorAttributeSearchResultsInterface
     */
    public function getJoinedList(SearchCriteriaInterface $criteria)
    {
        $collection = $this->collectionFactory->create();

        $eavAttribute = $collection->getTable('eav_attribute');
        $collection->getSelect()->joinLeft(
            $eavAttribute.' as eav',
            'main_table.attribute_id = eav.attribute_id'
        );

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
     * @param \Webkul\MpVendorAttributeManager\Model\VendorAttribute $subject
     * @return boolean
     */
    public function delete(\Webkul\MpVendorAttributeManager\Model\VendorAttribute $subject)
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
