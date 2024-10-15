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
namespace Webkul\MpRmaSystem\Controller\Customer;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Webkul\MpRmaSystem\Helper\Data;
use Magento\Customer\Model\Session;

class Create extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Customer\Model\Url
     */
    protected $url;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $session;

    /**
     * @var \Webkul\MpRmaSystem\Helper\Data
     */
    protected $mpRmaHelper;

    /**
     * @var \Webkul\MpRmaSystem\Model\DetailsFactory
     */
    protected $details;

    /**
     * @var \Magento\Customer\Model\Customer
     */
    protected $customer;

    /**
     * @var \Magento\Sales\Model\Order\Item
     */
    protected $orderItem;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $fileSystem;

    /**
     * @var \Magento\Sales\Model\Order\Item
     */
    protected $items;

    /**
     * Initialize Dependencies
     *
     * @param Context $context
     * @param \Magento\Customer\Model\Url $url
     * @param \Magento\Customer\Model\Session $session
     * @param \Webkul\MpRmaSystem\Helper\Data $mpRmaHelper
     * @param \Webkul\MpRmaSystem\Model\DetailsFactory $details
     * @param \Webkul\MpRmaSystem\Model\ItemsFactory $items
     * @param \Magento\Customer\Model\Customer $customer
     * @param \Magento\Sales\Model\Order\Item $orderItem
     * @param \Magento\Framework\Filesystem $fileSystem
     * @param \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory
     * @return void
     */
    public function __construct(
        Context $context,
        \Magento\Customer\Model\Url $url,
        \Magento\Customer\Model\Session $session,
        \Webkul\MpRmaSystem\Helper\Data $mpRmaHelper,
        \Webkul\MpRmaSystem\Model\DetailsFactory $details,
        \Webkul\MpRmaSystem\Model\ItemsFactory $items,
        \Magento\Customer\Model\Customer $customer,
        \Magento\Sales\Model\Order\Item $orderItem,
        \Magento\Framework\Filesystem $fileSystem,
        \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory
    ) {
        $this->url          = $url;
        $this->session      = $session;
        $this->mpRmaHelper  = $mpRmaHelper;
        $this->details      = $details;
        $this->items        = $items;
        $this->customer     = $customer;
        $this->orderItem    = $orderItem;
        $this->fileSystem   = $fileSystem;
        $this->fileUploader = $fileUploaderFactory;
        parent::__construct($context);
    }

    /**
     * Check customer authentication.
     *
     * @param RequestInterface $request
     *
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function dispatch(RequestInterface $request)
    {
        $loginUrl = $this->url->getLoginUrl();
        if (!$this->session->authenticate($loginUrl)) {
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
        }

        return parent::dispatch($request);
    }

    /**
     * Create Rma Action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $helper = $this->mpRmaHelper;
        if (!$this->getRequest()->isPost()) {
            $this->messageManager->addError(__('Something went wrong.'));
            return $this->resultRedirectFactory->create()->setPath('*/*/allrma');
        }

        $error = false;
        $msg = '';
        $allowedExtensions = ['png', 'jpg', 'jpeg', 'gif'];
        $rmaData = $this->getRequest()->getParams();
        if (!empty($rmaData)) {
            //try {
                $this->createRma($rmaData);
            // } catch (\Exception $e) {
            //     $this->messageManager->addError(__('There is some problem in generating RMA.'));
            //     $this->messageManager->addError(__($e->getMessage()));
            // }
        } else {
            $this->messageManager->addError(__('Something went wrong.'));
        }

        return $this->resultRedirectFactory->create()->setPath('*/*/allrma');
    }

    /**
     * Get Order Item Details
     *
     * @param int $itemId
     * @return array
     */
    public function getOrderItemDetails($itemId)
    {
        $orderItemDetails = $this->orderItem->load($itemId);
        return $orderItemDetails;
    }

     /**
      * Create Rma for the product
      *
      * @param array $rmaData
      * @return \Magento\Framework\Controller\Result\RedirectFactory
      */
    public function createRma($rmaData)
    {
        $helper = $this->mpRmaHelper;
        $orderId = $rmaData['order_id'];
        $time = date('Y-m-d H:i:s');
        $allPrices = [];
        $productIds = [];
        $allQtys = $rmaData['total_qty'];
        $allReasons = $rmaData['reason_ids'];
        $productId = 0;
        $sellerId  = 0;
        foreach ($rmaData['item_ids'] as $itemId) {
            $orderItem = $this->getOrderItemDetails($itemId);
            $productId = $orderItem->getProductId();
            $allPrices[$itemId] = $orderItem->getPrice();
            $productIds[$itemId] = $productId;
            $qty = $allQtys[$itemId];
            if (!$this->mpRmaHelper->isRmaAllowed($itemId, $orderId, $qty)) {
                $this->messageManager->addError(__('Quantity not allowed for RMA.'));
                return $this->resultRedirectFactory->create()->setPath('*/*/newrma');
            }
        }
        $rmaData['product_id'] = implode(",", $productIds);
        if ($this->mpRmaHelper->isMpAssign()) {
            $sellerId = $this->mpRmaHelper->isMpAssignOrderSeller($orderId);
        }
        if (!$sellerId) {
            $sellerId = $this->mpRmaHelper->getSellerIdByProductId($productId, $orderId);
        }
        
        $customerId = $this->mpRmaHelper->getCustomerId();
        $customer = $this->customer->load($customerId);
        $email = $customer->getEmail();
        $customerName = $customer->getName();
        $order = $this->mpRmaHelper->getOrder($orderId);
        $rmaData['seller_id'] = $sellerId;
        if ($sellerId) {
            $rmaData['product_seller'] = 'Seller';
        } else {
            $rmaData['product_seller'] = 'Admin';
        }
        $rmaData['customer_id'] = $customerId;
        $rmaData['customer_email'] = $email;
        $rmaData['customer_name'] = $customerName;
        $rmaData['seller_status'] = Data::SELLER_STATUS_PENDING;
        $rmaData['status'] = Data::RMA_STATUS_PENDING;
        $rmaData['updated_date'] = $time;
        $rmaData['created_date'] = $time;
        $rmaData['order_ref'] = '#'.$order->getIncrementId();
        $numberOfImages = (int) $rmaData['is_checked'];
        if ($rmaData['is_virtual']) {
            $rmaData['order_status'] = 2;
        }

        if (!$helper->isAllowedImageUpload($numberOfImages)) {
            $msg = __('Error in image uploading.');
            $this->messageManager->addError(__($msg));
            return $this->resultRedirectFactory->create()->setPath('*/*/newrma');
        }

        $model = $this->details->create();
        $model->setData($rmaData)->save();
        $rmaId = $model->getId();
        $helper->uploadImages($numberOfImages, $rmaId);
        if (!$helper->setItemsData($productIds, $allReasons, $allQtys, $allPrices, $rmaId)) {
            $model->delete();
            $this->messageManager->addError(__('There is some problem in generating RMA.'));
            return $this->resultRedirectFactory->create()->setPath('*/*/newrma');
        }
        $helper->setRegistry("rma_id", $rmaId);
        //Start: Send Email After RMA Creation
        $rmaInfo = $rmaData;
        $rmaInfo['rma_id'] = $rmaId;
        $details = [
                    'type' => 0,
                    'name' => $customerName,
                    'rma' => $rmaInfo
                ];
        $helper->sendNewRmaEmail($details);
        //End: Send Email After RMA Creation
        //order parital cancel items process
        $isCancelItems = $rmaData["resolution_type"];
        $isPartialCancel = $rmaData["is_partial_cancel"];
        if ($isCancelItems==3) {
            $sellerRmaData = [];
            $finalStatus = 0;
            $returnQty = false;
            $sellerStatus = 4;
            $sellerRmaData['status'] = Data::RMA_STATUS_SOLVED;
            $sellerRmaData['final_status'] = Data::FINAL_STATUS_CLOSED;
            $sellerRmaData['seller_status'] = $sellerStatus;
            $rma = $this->details->create()->load($rmaId);
            $productIds = explode(",", $rma->getProductId());
            $rma->addData($sellerRmaData)->setId($rmaId)->save();
            $order = $helper->getOrder($rma->getOrderId());
            $data["rma_id"] = $rmaId;
            $data["seller_status"] = $sellerStatus;
            $helper->sendUpdateRmaEmail($data);
            if ($returnQty) {
                $helper->processCancellation($rmaId);
                $helper->updateRmaItemQtyStatus($rmaId);
            }
            if ($isPartialCancel) {
                //new order process
                $quoteId = $order->getQuoteId();
                $newOrderId = $helper->createOrder($rmaData, $quoteId, $order);
                //end new order process
                if ($newOrderId) {
                    $this->messageManager->addSuccess(__('New Order has been generated'));
                    $params = ['id' => $rmaId];
                    return $this->resultRedirectFactory->create()->setPath('*/*/rma', $params);
                } else {
                    $this->messageManager->addSuccess(__('Error in creating new order.'));
                    $params = ['id' => $rmaId];
                    return $this->resultRedirectFactory->create()->setPath('*/*/rma', $params);
                }
            //end order parital cancel items process
            } else {
                $order->setStatus('canceled');
                $order->save();
                $this->messageManager->addSuccess(__('Order has been canceled.'));
                $params = ['id' => $rmaId];
                return $this->resultRedirectFactory->create()->setPath('*/*/rma', $params);
            }
            
        }
        $this->messageManager->addSuccess(__('New RMA request generated.'));
        $params = ['id' => $rmaId];
        return $this->resultRedirectFactory->create()->setPath('*/*/rma', $params);
    }
}
