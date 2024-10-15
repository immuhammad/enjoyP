<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpRmaSystem
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpRmaSystem\Controller\Adminhtml\Rma;

use Webkul\MpRmaSystem\Helper\Data;

class Update extends \Webkul\MpRmaSystem\Controller\Adminhtml\Rma
{
    /**
     * @var \Webkul\MpRmaSystem\Helper\Data
     */
    protected $mpRmaHelper;

    /**
     * @var \Webkul\MpRmaSystem\Model\DetailsFactory
     */
    protected $details;

    /**
     * Initialize Dependencies
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Webkul\MpRmaSystem\Helper\Data $mpRmaHelper
     * @param \Webkul\MpRmaSystem\Model\DetailsFactory $details
     * @param \Webkul\MpRmaSystem\Model\ItemsFactory $items
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @return void
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Webkul\MpRmaSystem\Helper\Data $mpRmaHelper,
        \Webkul\MpRmaSystem\Model\DetailsFactory $details,
        \Webkul\MpRmaSystem\Model\ItemsFactory $items,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
    ) {
        $this->mpRmaHelper   = $mpRmaHelper;
        $this->details       = $details;
        $this->items         = $items;
        $this->stockRegistry = $stockRegistry;
        parent::__construct($context);
    }

    /**
     * Update Rma Action
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        if (!$this->getRequest()->getParam('rma_id')) {
            return $this->resultRedirectFactory
                    ->create()
                    ->setPath('*/rma/index');
        }

        $helper = $this->mpRmaHelper;
        $data = $this->getRequest()->getParams();
        $rmaData = [];
        $finalStatus = 0;
        $rmaId = $data['rma_id'];
        $IsCustomer = $helper->getCustmerByRmaId($rmaId);
        $returnQty = false;

        if (!$IsCustomer) {
            $this->messageManager->addError(__("Customer not exists"));
            return $this->resultRedirectFactory
                    ->create()
                    ->setPath(
                        '*/rma/edit',
                        ['id' => $rmaId, 'back' => null, '_current' => true]
                    );
        }
        $sellerStatus = $data['seller_status'];
        if ($sellerStatus == Data::SELLER_STATUS_PENDING || $sellerStatus == Data::SELLER_STATUS_PACKAGE_NOT_RECEIVED) {
            $rmaData['status'] = Data::RMA_STATUS_PENDING;
        } elseif ($sellerStatus == Data::SELLER_STATUS_PACKAGE_RECEIVED) {
            $rmaData['status'] = Data::RMA_STATUS_PROCESSING;
        } elseif ($sellerStatus == Data::SELLER_STATUS_PACKAGE_DISPATCHED) {
            $rmaData['status'] = Data::RMA_STATUS_SOLVED;
        } elseif ($sellerStatus == Data::SELLER_STATUS_SOLVED) {
            $rmaData['status'] = Data::RMA_STATUS_SOLVED;
        } elseif ($sellerStatus == Data::SELLER_STATUS_ITEM_CANCELED) {
            $rmaData['status'] = Data::RMA_STATUS_SOLVED;
            $rmaData['final_status'] = Data::FINAL_STATUS_SOLVED;
            $returnQty = true;
        } else {
            $rmaData['status'] = Data::RMA_STATUS_DECLINED;
            $rmaData['final_status'] = Data::FINAL_STATUS_DECLINED;
        }

        $productDetails = $helper->getRmaProductDetails($rmaId);

        $rmaData['seller_status'] = $sellerStatus;
        $rma = $this->details->create()->load($rmaId);
        $productIds = explode(",", $rma->getProductId());
        if (isset($data['return_to_stock'])) {
            foreach ($productDetails as $product) {
                $stockItem=$this->stockRegistry->getStockItem($product->getProductId());
                $newStockQty = $stockItem->getQty() + $product->getQty();
                $stockItem->setData("qty", $newStockQty);
                $stockItem->save();
                $itemColl = $this->items->create()->getCollection()
                                    ->addFieldToFilter('rma_id', $rmaId)
                                    ->addFieldToFilter('product_id', $product->getProductId());
                foreach ($itemColl as $item) {
                    $item->setIsQtyReturned(1);
                    $item->save();
                }
            }
        }
        $rma->addData($rmaData)->setId($rmaId)->save();
        if ($returnQty) {
            $helper->processCancellation($rmaId);
            $helper->updateRmaItemQtyStatus($rmaId);
        }
        $helper->sendUpdateRmaEmail($data);
        return $this->resultRedirectFactory
                    ->create()
                    ->setPath(
                        '*/rma/edit',
                        ['id' => $rmaId, 'back' => null, '_current' => true]
                    );
    }
}
