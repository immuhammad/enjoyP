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
 * Stripe StripeCustomerRepository Class
 */
class StripeCustomerRepository implements \Webkul\MpStripe\Api\StripeCustomerRepositoryInterface
{

    /**
     * @var Object $modelFactory
     */
    protected $modelFactory = null;

    /**
     * @var Object $collectionFactory
     */
    protected $collectionFactory = null;

    /**
     * initialize
     *
     * @param \Webkul\MpStripe\Model\StripeCustomerFactory $modelFactory
     * @param Webkul\MpStripe\Model\ResourceModel\StripeCustomer\CollectionFactory $collectionFactory
     * @return void
     */
    public function __construct(
        \Webkul\MpStripe\Model\StripeCustomerFactory $modelFactory,
        \Webkul\MpStripe\Model\ResourceModel\StripeCustomer\CollectionFactory $collectionFactory
    ) {
        $this->modelFactory = $modelFactory;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Get by id
     *
     * @param int $id
     * @return \Webkul\MpStripe\Model\StripeCustomer
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
     * Get by id
     *
     * @param \Webkul\MpStripe\Model\StripeCustomer $subject
     * @return \Webkul\MpStripe\Model\StripeCustomer
     */
    public function save(\Webkul\MpStripe\Model\StripeCustomer $subject)
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
     * @param \Webkul\MpStripe\Model\StripeCustomer $subject
     * @return boolean
     */
    public function delete(\Webkul\MpStripe\Model\StripeCustomer $subject)
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
