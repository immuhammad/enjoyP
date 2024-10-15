<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpStripe
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpStripe\Controller\Adminhtml\Capture;

use Magento\Framework\App\Config\ScopeConfigInterface;
use \Magento\Catalog\Model\Product\Visibility;
use Magento\Store\Model\StoreManagerInterface;
use Webkul\MpStripe\Controller\Payment\CreateIntent;
use Magento\Framework\Exception\LocalizedException;

class Process extends \Magento\Backend\App\Action
{
    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonResultFactory
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Webkul\MpStripe\Helper\Data $helper
     * @param \Webkul\Marketplace\Helper\Data $mpHelper
     * @param StoreManagerInterface $storeManager
     * @param \Magento\Framework\Url $urlHelper
     * @param \Magento\Sales\Model\OrderRepository $orderRepository
     * @param \Magento\Framework\App\Config\Storage\WriterInterface $configWriter
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Webkul\MpStripe\Model\StripeSellerRepository $sellerKeys
     * @param CreateIntent $createIntent
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $jsonResultFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Webkul\MpStripe\Helper\Data $helper,
        \Webkul\Marketplace\Helper\Data $mpHelper,
        StoreManagerInterface $storeManager,
        \Magento\Framework\Url $urlHelper,
        \Magento\Sales\Model\OrderRepository $orderRepository,
        \Magento\Framework\App\Config\Storage\WriterInterface $configWriter,
        \Psr\Log\LoggerInterface $logger,
        \Webkul\MpStripe\Model\StripeSellerRepository $sellerKeys,
        CreateIntent $createIntent
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->messageManager = $messageManager;
        $this->_logger = $logger;
        $this->jsonResultFactory = $jsonResultFactory;
        $this->configWriter = $configWriter;
        $this->orderRepository = $orderRepository;
        $this->storeManager = $storeManager;
        $this->urlHelper = $urlHelper;
        $this->helper = $helper;
        $this->createIntent = $createIntent;
        $this->mpHelper = $mpHelper;
        $this->sellerKeys = $sellerKeys;
        parent::__construct($context);
    }

    /**
     * Execute webhook creation
     */
    public function execute()
    {
        $request = $this->getRequest()->getParams();
        $orderId = $request['id'];
        $order = $this->orderRepository->get($orderId);
        if ($order->canInvoice()) {
            $paymentIntent = $request['payment_intent'];
            $orderItems = $order->getAllItems();
            $productId = "";
            foreach ($orderItems as $item) {
                $productId = $item->getProductId();
                break;
            }
            $sellerId = $this->mpHelper->getSellerIdByProductId($productId);
            $this->helper->setUpDefaultDetails();
            if ($sellerId != 0 &&
                $this->helper->isDirectCharge()
            ) {
                $collectionData = $this->sellerKeys->getBySellerId($sellerId);
                $paymentIntent = \Stripe\PaymentIntent::retrieve(
                    $paymentIntent,
                    ['stripe_account' => $collectionData["stripe_user_id"]]
                );
            } else {
                $paymentIntent = \Stripe\PaymentIntent::retrieve(
                    $paymentIntent
                );
            }
            $finalCart = $this->helper->getFinalCart($order);
            $finalCartData = $this->helper->getCheckoutFinalData($finalCart, $order);
            $ifSellerInCart = $this->helper->getIfSellerInCart($finalCartData);
            try {
                $captureData = $paymentIntent->capture();
                $response = [];
                if ($ifSellerInCart && !$this->helper->isDirectCharge()) {
                    foreach ($finalCartData as $sellerId => $paymentDetail) {
                        if (!empty($paymentDetail['cart']['stripe_user_id'])) {
                            $response[$sellerId] = $this->createIntent->createStripeTransferCharge(
                                $paymentDetail,
                                $paymentIntent['charges']['data'][0]
                            );
                        }
                    }
                }
                $this->messageManager->addSuccess(
                    __('Order captured successfully, invoices will be generated automatically')
                );
            } catch (\Exception $e) {
                $this->_logger->info('capture controller '.$e->getMessage());
                $this->messageManager->addError(
                    __('There was an error capturing the transaction')
                );
            }
        } else {
            $this->messageManager->addError(
                __('The order has already been captured successfully')
            );
            return $this->resultRedirectFactory->create()->setPath(
                'sales/order/view',
                ['order_id' => $orderId, '_secure' => $this->getRequest()->isSecure()]
            );
        }

        return $this->resultRedirectFactory->create()->setPath(
            'sales/order/view',
            ['order_id' => $orderId, '_secure' => $this->getRequest()->isSecure()]
        );
    }
}
