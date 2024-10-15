<?php
/**
 * Webkul Software.
 *
 * @category Webkul
 * @package Webkul_MpStripe
 * @author Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */


namespace Webkul\MpStripe\Model;

/**
 * Stripe StripeSellerRepository Class
 */
class StripeSellerRepository implements \Webkul\MpStripe\Api\StripeSellerRepositoryInterface
{

    /**
     * @var \Webkul\MpStripe\Model\StripeSellerFactory
     */
    protected $modelFactory = null;

    /**
     * @var \Webkul\MpStripe\Model\ResourceModel\StripeSeller\CollectionFactory
     */
    protected $collectionFactory = null;

    /**
     * initialize
     *
     * @param \Webkul\MpStripe\Model\StripeSellerFactory $modelFactory
     * @param Webkul\MpStripe\Model\ResourceModel\StripeSeller\CollectionFactory $collectionFactory
     * @return void
     */
    public function __construct(
        \Webkul\MpStripe\Model\StripeSellerFactory $modelFactory,
        \Webkul\MpStripe\Model\ResourceModel\StripeSeller\CollectionFactory $collectionFactory
    ) {
        $this->modelFactory = $modelFactory;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Get by id
     *
     * @param int $id
     * @return \Webkul\MpStripe\Model\StripeSeller
     */
    public function getById($id)
    {
        $model = $this->modelFactory->create()->load($id);
        if (!$model->getId()) {
            throw new \Magento\Framework\Exception\NoSuchEntityException(
                __('The CMS block with the "%1" ID doesn\'t exist.', $id)
            );
        }
        return $model;
    }

    /**
     * Save
     *
     * @param \Webkul\MpStripe\Model\StripeSeller $subject
     * @return \Webkul\MpStripe\Model\StripeSeller
     */
    public function save(\Webkul\MpStripe\Model\StripeSeller $subject)
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
     * @param \Webkul\MpStripe\Model\StripeSeller $subject
     * @return boolean
     */
    public function delete(\Webkul\MpStripe\Model\StripeSeller $subject)
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

    /**
     * Get By Seller Id
     *
     * @param int $sellerId
     * @return boolean
     */
    public function getBySellerId($sellerId)
    {
        $stripeSeller = $this->modelFactory->create()->getCollection()
            ->addFieldToFilter("seller_id", ["eq" => $sellerId])
            ->getFirstItem()->getData();
        if (empty($stripeSeller)) {
            return false;
        }
        return $stripeSeller;
    }
}
