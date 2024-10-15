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

namespace Webkul\MpAdvancedBookingSystem\Controller\Adminhtml\Cancellation;

use Magento\Sales\Model\Order\Email\Sender\CreditmemoSender;

class ProcessCancellation extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Sales\Controller\Adminhtml\Order\CreditmemoLoader
     */
    protected $creditmemoLoader;

    /**
     * @var Webkul\MpAdvancedBookingSystem\Model\ResourceModel\Info\CollectionFactory
     */
    protected $infoCollFactory;

    /**
     * @var Magento\Sales\Model\Order\ItemFactory
     */
    protected $itemFactory;

    /**
     * @var CreditmemoSender
     */
    protected $creditmemoSender;

    /**
     * @var \Webkul\MpAdvancedBookingSystem\Helper\Data
     */
    protected $helper;

    /**
     * Constructor
     *
     * @param \Magento\Backend\App\Action\Context                                        $context
     * @param \Webkul\MpAdvancedBookingSystem\Helper\Data                                $helper
     * @param \Magento\Sales\Controller\Adminhtml\Order\CreditmemoLoader                 $creditmemoLoader
     * @param \Webkul\MpAdvancedBookingSystem\Model\ResourceModel\Info\CollectionFactory $infoCollFactory
     * @param \Magento\Sales\Model\Order\ItemFactory                                     $itemFactory
     * @param CreditmemoSender                                                           $creditmemoSender
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Webkul\MpAdvancedBookingSystem\Helper\Data $helper,
        \Magento\Sales\Controller\Adminhtml\Order\CreditmemoLoader $creditmemoLoader,
        \Webkul\MpAdvancedBookingSystem\Model\ResourceModel\Info\CollectionFactory $infoCollFactory,
        \Magento\Sales\Model\Order\ItemFactory $itemFactory,
        CreditmemoSender $creditmemoSender
    ) {
        $this->helper = $helper;
        $this->creditmemoLoader = $creditmemoLoader;
        $this->creditmemoSender = $creditmemoSender;
        $this->infoCollFactory = $infoCollFactory;
        $this->itemFactory = $itemFactory;
        parent::__construct($context);
    }

    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $paramsData = $this->getRequest()->getParams();
        $data = [];
        try {
            $cancellationInfo = $this->helper->getCancellationInfoById($paramsData['cancellation_id']);
            if (!empty($cancellationInfo->getData())) {
                $creditmemo = [];
                $creditmemo['do_offline'] = 1;
                $creditmemo['comment_text'] = '';
                $creditmemo['shipping_amount'] = 0;
                $creditmemo['adjustment_positive'] = 0;
                $creditmemo['send_email'] = (isset($paramsData['send_email'])) ? 1 : null;
                $creditmemo['adjustment_negative'] = $cancellationInfo->getCancellationCharge();
                $creditmemo['items'] = [
                    $paramsData['item_id'] => [
                        'qty' => $paramsData['qty']
                    ]
                ];
                $data = $creditmemo;
            }

            $this->creditmemoLoader->setOrderId($this->getRequest()->getParam('order_id'));
            $this->creditmemoLoader->setCreditmemo($data);
            $creditmemo = $this->creditmemoLoader->load();
            if ($creditmemo) {
                if (!$creditmemo->isValidGrandTotal()) {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __('The credit memo\'s total must be positive.')
                    );
                }

                if (isset($data['do_offline'])) {
                    //do not allow online refund for Refund to Store Credit
                    if (!$data['do_offline'] && !empty($data['refund_customerbalance_return_enable'])) {
                        throw new \Magento\Framework\Exception\LocalizedException(
                            __('Cannot create online refund for Refund to Store Credit.')
                        );
                    }
                }
                
                if (!empty($data['comment_text'])) {
                    $creditmemo->addComment(
                        $data['comment_text'],
                        isset($data['comment_customer_notify']),
                        isset($data['is_visible_on_front'])
                    );

                    $creditmemo->setCustomerNote($data['comment_text']);
                    $creditmemo->setCustomerNoteNotify(isset($data['comment_customer_notify']));
                }

                $creditmemoManagement = $this->_objectManager->create(
                    \Magento\Sales\Api\CreditmemoManagementInterface::class
                );
                $creditmemo->getOrder()->setCustomerNoteNotify(!empty($data['send_email']));
                $creditmemoManagement->refund($creditmemo, (bool)$data['do_offline']);

                if (!empty($data['send_email'])) {
                    $this->creditmemoSender->send($creditmemo);
                }
                $cancellationInfo->setStatus(1)
                    ->setCreditMemoId($creditmemo->getId())
                    ->save();
                $itemModel = $this->itemFactory->create()->load($paramsData['item_id']);
                $productId = $itemModel->getProductId();
                $bookingInfo = $this->helper->getBookingInfo($productId);
                $total_slot = $bookingInfo['total_slots'] + $paramsData['qty'];
                $this->helper->setInStock($productId, $total_slot);
                $infoItem = $this->infoCollFactory->create()
                                      ->addFieldToFilter('id', ['eq'=>$bookingInfo['id']])
                                      ->getFirstItem();
                $infoItem->setTotalSlots($total_slot);
                $infoItem->save();
                $this->messageManager->addSuccessMessage(__('You created the credit memo.'));
                $resultRedirect->setPath('*/*/requests');
                return $resultRedirect;
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->_objectManager->get(\Psr\Log\LoggerInterface::class)->critical($e);
            $this->messageManager->addErrorMessage(__('We can\'t save the credit memo right now.'));
        }

        $resultRedirect->setPath('*/*/requests');
        return $resultRedirect;
    }
}
