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
use Magento\Framework\View\Result\PageFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\InputException;
use Magento\Sales\Helper\Guest as GuestHelper;
use Magento\Framework\Controller\ResultInterface;

/**
 * Booking cancellation page controller
 */
class GuestIndex extends Action
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
     * @var GuestHelper
     */
    protected $guestHelper;

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
     * @param GuestHelper                                 $guestHelper
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Magento\Customer\Model\Url $url,
        \Magento\Customer\Model\Session $customerSession,
        \Webkul\MpAdvancedBookingSystem\Helper\Data $helper,
        \Magento\Framework\Registry $coreRegistry,
        OrderRepositoryInterface $orderRepository,
        GuestHelper $guestHelper
    ) {
        $this->_resultPageFactory = $resultPageFactory;
        $this->_url = $url;
        $this->_customerSession = $customerSession;
        $this->_helper = $helper;
        $this->_coreRegistry = $coreRegistry;
        $this->orderRepository = $orderRepository;
        $this->guestHelper = $guestHelper;
        parent::__construct($context);
    }

    /**
     * Execute
     *
     * @return \Magento\Framework\Controller\Result\Page
     */
    public function execute()
    {
        $result = $this->guestHelper->loadValidOrder($this->getRequest());
        if ($result instanceof ResultInterface) {
            return $result;
        }

        if (!empty($this->getRequest()->getParam('id'))) {
            try {
                $itemId = $this->getRequest()->getParam('id');
                $item = $this->_helper->getOrderItem($itemId);
                $status = $this->_helper->getCancellationStatus($item->getOrderId(), $item->getId());
                $status = $this->_initOrder($item->getOrderId());
            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                $this->messageManager->addError($e->getMessage());
                $status = false;
            } catch (\Exception $e) {
                $status = false;
            }

            if ($status && $item->getIsCancellationAvailable() && $item->canRefund()) {
                $resultPage = $this->_resultPageFactory->create();
                $orderId = $this->getRequest()->getParam('order_id');
                $order = $this->orderRepository->get($orderId);
                $title = __('Booking Cancellation (Order # '. $order->getIncrementId().')');
                $resultPage->getConfig()->getTitle()->set($title);
                return $resultPage;
            } else {
                $this->messageManager->addError(__("Cancellation is not allowed"));
            }
        } else {
            $this->messageManager->addError(__("Order Item not Found"));
        }

        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('sales/guest/form/');
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
        } catch (NoSuchEntityException $e) {
            $this->messageManager->addErrorMessage(__('This order no longer exists.'));
            return false;
        } catch (InputException $e) {
            $this->messageManager->addErrorMessage(__('This order no longer exists.'));
            return false;
        }

        return true;
    }
}
