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
namespace Webkul\MpAdvancedBookingSystem\Controller\Cancellation;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\InputException;
use Webkul\MpAdvancedBookingSystem\Model\CancellationFactory;

/**
 * Booking cancellation page controller
 */
class Index extends Action
{
    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Webkul\MpAdvancedBookingSystem\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var CancellationFactory
     */
    protected $cancellationFactory;

    /**
     * @var \Magento\Customer\Model\Url
     */
    protected $_url;

    /**
     * Constructor
     *
     * @param Context                                     $context
     * @param PageFactory                                 $resultPageFactory
     * @param \Magento\Customer\Model\Url                 $url
     * @param \Magento\Customer\Model\Session             $customerSession
     * @param \Webkul\MpAdvancedBookingSystem\Helper\Data $helper
     * @param \Magento\Framework\Registry                 $coreRegistry
     * @param OrderRepositoryInterface                    $orderRepository
     * @param CancellationFactory                         $cancellationFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Magento\Customer\Model\Url $url,
        \Magento\Customer\Model\Session $customerSession,
        \Webkul\MpAdvancedBookingSystem\Helper\Data $helper,
        \Magento\Framework\Registry $coreRegistry,
        OrderRepositoryInterface $orderRepository,
        CancellationFactory $cancellationFactory
    ) {
        $this->_resultPageFactory = $resultPageFactory;
        $this->_url = $url;
        $this->_customerSession = $customerSession;
        $this->_helper = $helper;
        $this->_coreRegistry = $coreRegistry;
        $this->orderRepository = $orderRepository;
        $this->cancellationFactory = $cancellationFactory;
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
        $loginUrl = $this->_url->getLoginUrl();
        if (!$this->_customerSession->authenticate($loginUrl)) {
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
        }

        return parent::dispatch($request);
    }

    /**
     * Execute
     *
     * @return \Magento\Framework\Controller\Result\Page
     */
    public function execute()
    {
        if (!empty($this->getRequest()->getParam('id'))) {
            $message = '';
            try {
                $itemId = $this->getRequest()->getParam('id');
                $orderId = $this->getRequest()->getParam('order_id');
                $item = $this->_helper->getOrderItem($itemId);
                $this->matchProductIds($orderId, $item->getOrderId(), $itemId);
                $status = $this->_helper->getCancellationStatus($item->getOrderId(), $item->getId());
                $orderStatus = $this->_initOrder($item->getOrderId());
            } catch (NoSuchEntityException $e) {
                $status = $orderStatus= false;
                $message = $e->getMessage();
            }
            if ($status && $orderStatus && $item->getIsCancellationAvailable() && $item->canRefund()) {
                $order = $this->_coreRegistry->registry('current_order');
                $resultPage = $this->_resultPageFactory->create();
                $title = __('Booking Cancellation (Order # '. $order->getIncrementId().')');
                $resultPage->getConfig()->getTitle()->set($title);
                return $resultPage;
            } else {
                $this->messageManager->addError($message ?? __("Cancellation is not allowed"));
            }
        } else {
            $this->messageManager->addError(__("Order Item not Found"));
        }

        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('sales/order/history/');
    }

    /**
     * Match product Ids
     *
     * @param int $orderId
     * @param int $itemOrderId
     * @param int $itemId
     * @throws NoSuchEntityException
     */
    protected function matchProductIds($orderId, $itemOrderId, $itemId)
    {
        if ($orderId != $itemOrderId) {
            throw new NoSuchEntityException(__('This order no longer exists.'));
        }

        $collection = $this->cancellationFactory->create()->getCollection()
            ->addFieldToFilter('order_id', $orderId)
            ->addFieldToFilter('order_item_id', $itemId);
        
        if ($collection->getSize() > 0) {
            throw new NoSuchEntityException(__('Cancellation request already created.'));
        }
    }

    /**
     * Initialize order in registry
     *
     * @param int $id
     * @return boolean
     */
    protected function _initOrder($id = null)
    {
        try {
            $order = $this->orderRepository->get($id);
            if ($order->getCustomerId() != $this->_customerSession->getId()) {
                throw new NoSuchEntityException(__('This order no longer exists.'));
            }
        } catch (NoSuchEntityException $e) {
            $this->messageManager->addErrorMessage(__('This order no longer exists.'));
            return false;
        } catch (InputException $e) {
            $this->messageManager->addErrorMessage(__('This order no longer exists.'));
            return false;
        }

        $this->_coreRegistry->register('current_order', $order);
        return true;
    }
}
