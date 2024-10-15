<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpAdvancedBookingSystem
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpAdvancedBookingSystem\Observer;

use Magento\Framework\Event\ObserverInterface;

class CatalogProductDeleteAfterObserver implements ObserverInterface
{

    /**
     * @var \Webkul\MpAdvancedBookingSystem\Model\ResourceModel\Info\CollectionFactory
     */
    protected $infoCollectionFactory;

    /**
     * @var \Webkul\MpAdvancedBookingSystem\Model\ResourceModel\Slot\CollectionFactory
     */
    protected $slotCollectionFactory;

    /**
     * Constructor
     *
     * @param \Webkul\MpAdvancedBookingSystem\Model\ResourceModel\Info\CollectionFactory $infoCollectionFactory
     * @param \Webkul\MpAdvancedBookingSystem\Model\ResourceModel\Slot\CollectionFactory $slotCollectionFactory
     */
    public function __construct(
        \Webkul\MpAdvancedBookingSystem\Model\ResourceModel\Info\CollectionFactory $infoCollectionFactory,
        \Webkul\MpAdvancedBookingSystem\Model\ResourceModel\Slot\CollectionFactory $slotCollectionFactory
    ) {
        $this->infoCollectionFactory = $infoCollectionFactory;
        $this->slotCollectionFactory = $slotCollectionFactory;
    }
    /**
     * After delete product event handler.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $product = $observer->getProduct();
        $productId = $product->getId();
        $productType = $product->getTypeId();
        if ($productType == "booking" || $productType == "hotelbooking") {
            $infoCollection = $this->infoCollectionFactory->create()
                ->addFieldToFilter('product_id', $productId);
            $this->deleteBookingEntry($infoCollection);
            $slotCollection = $this->slotCollectionFactory->create()
                ->addFieldToFilter('product_id', $productId);
            $this->deleteBookingEntry($slotCollection);

        }
    }

    /**
     * DeleteBookingEntry
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection $collection
     */
    public function deleteBookingEntry($collection)
    {
        if ($collection->getSize()) {
            $collection->walk('delete');
        }
    }
}
