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
namespace Webkul\MpStripe\Controller\Order;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\App\RequestInterface;
use Webkul\MpStripe\Controller\Payment\CreateIntent;
use Magento\Framework\Exception\LocalizedException;

class Capture extends Action
{
    /**
     * @var PageFactory
     */
    private $resultPageFactory;
    /**
     * @var Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var \Webkul\MpStripe\Helper\Data
     */
    private $helper;

    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    private $marketplaceHelper;

    /**
     * @var \Magento\Customer\Model\Url
     */
    private $customerUrl;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Session $customerSession
     * @param \Webkul\MpStripe\Helper\Data $helper
     * @param \Webkul\Marketplace\Helper\Data $marketplaceHelper
     * @param \Psr\Log\LoggerInterface $logger
     * @param CreateIntent $createIntent
     * @param \Magento\Sales\Model\OrderRepository $orderRepository
     * @param \Webkul\MpStripe\Model\StripeSellerRepository $sellerKeys
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Session $customerSession,
        \Webkul\MpStripe\Helper\Data $helper,
        \Webkul\Marketplace\Helper\Data $marketplaceHelper,
        \Psr\Log\LoggerInterface $logger,
        CreateIntent $createIntent,
        \Magento\Sales\Model\OrderRepository $orderRepository,
        \Webkul\MpStripe\Model\StripeSellerRepository $sellerKeys
    ) {
        $this->helper = $helper;
        $this->marketplaceHelper = $marketplaceHelper;
        $this->customerSession = $customerSession;
        $this->resultPageFactory = $resultPageFactory;
        $this->orderRepository = $orderRepository;
        $this->_logger = $logger;
        $this->createIntent = $createIntent;
        $this->sellerKeys = $sellerKeys;
        parent::__construct($context);
    }

    /**
     * Connect to stripe.
     *
     * @return \Magento\Framework\View\Result\Page
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
            $sellerId = $this->marketplaceHelper->getSellerIdByProductId($productId);
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
                'marketplace/order/view',
                ['id' => $orderId, '_secure' => $this->getRequest()->isSecure()]
            );
        }

        return $this->resultRedirectFactory->create()->setPath(
            'marketplace/order/view',
            ['id' => $orderId, '_secure' => $this->getRequest()->isSecure()]
        );
    }
}
