<?php
/**
 * Webkul Software.
 *
 * @category Webkul
 * @package Webkul_MpServiceFee
 * @author Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */
namespace Webkul\MpServiceFee\Model;

class AttributeListRepository implements \Webkul\MpServiceFee\Api\AttributeListRepositoryInterface
{

    /**
     * @var \Webkul\MpServiceFee\Model\AttributesListFactory $modelFactory
     */
    protected $modelFactory = null;
    
    /**
     * @var \Webkul\MpServiceFee\Model\ResourceModel\AttributesList\CollectionFactory $collectionFactory
     */
    protected $collectionFactory = null;

    /**
     * Class constructor
     *
     * @param \Webkul\MpServiceFee\Model\AttributesListFactory $modelFactory
     * @param \Webkul\MpServiceFee\Model\ResourceModel\AttributesList\CollectionFactory $collectionFactory
     */
    public function __construct(
        \Webkul\MpServiceFee\Model\AttributesListFactory $modelFactory,
        \Webkul\MpServiceFee\Model\ResourceModel\AttributesList\CollectionFactory $collectionFactory
    ) {
        $this->modelFactory = $modelFactory;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Get by id
     *
     * @param int $id
     * @return \Webkul\MpServiceFee\Model\AttributesList
     */
    public function getById($id)
    {
        $model = $this->modelFactory->create()->load($id);
        if (!$model->getId()) {
            throw new \Magento\Framework\Exception\NoSuchEntityException(__('The "%1" ID doesn\'t exist.', $id));
        }
        return $model;
    }

    /**
     * Save the attribute list
     *
     * @param \Webkul\MpServiceFee\Model\AttributesList  $subject
     * @return \Webkul\MpServiceFee\Model\AttributesList
     */
    public function save(\Webkul\MpServiceFee\Model\AttributesList  $subject)
    {
        try {
            $subject->save();
        } catch (\Exception $exception) {
            throw new \Magento\Framework\Exception\CouldNotSaveException(__($exception->getMessage()));
        }
        return $subject;
    }

    /**
     * Get list
     *
     * @param Magento\Framework\Api\SearchCriteriaInterface $creteria
     * @return Magento\Framework\Api\SearchResults
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $creteria)
    {
        $collection = $this->collectionFactory->create();
        return $collection;
    }

    /**
     * Delete
     *
     * @param \Webkul\MpServiceFee\Model\AttributesList  $subject
     * @return boolean
     */
    public function delete(\Webkul\MpServiceFee\Model\AttributesList  $subject)
    {
        try {
            $subject->delete();
        } catch (\Exception $exception) {
            throw new \Magento\Framework\Exception\CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    /**
     * Delete by id
     *
     * @param int $id
     * @return boolean
     */
    public function deleteById($id)
    {
        return $this->delete($this->getById($id));
    }
}
