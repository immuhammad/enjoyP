<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_B2BMarketplace
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\B2BMarketplace\Controller\Supplier;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Webkul\B2BMarketplace\Model\ResourceModel\Quotation\CollectionFactory as QuotationCollectionFactory;
use Webkul\B2BMarketplace\Api\QuoteManagementInterface;

class MessagePost extends Action
{
    /**
     * @var FormKeyValidator
     */
    private $_formKeyValidator;

    /**
     * @var QuotationCollectionFactory
     */
    private $quotationCollectionFactory;

    /**
     * @var QuoteManagementInterface
     */
    private $b2bQuoteManagement;

    /**
     * @param Context $context
     * @param \Webkul\B2BMarketplace\Model\SupplierFactory $supplierFactory
     * @param \Webkul\B2BMarketplace\Helper\Data $helper
     * @param FormKeyValidator $formKeyValidator
     * @param QuotationCollectionFactory $quotationCollectionFactory
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param QuoteManagementInterface $b2bQuoteManagement
     */
    public function __construct(
        Context $context,
        \Webkul\B2BMarketplace\Model\SupplierFactory $supplierFactory,
        \Webkul\B2BMarketplace\Helper\Data $helper,
        FormKeyValidator $formKeyValidator,
        QuotationCollectionFactory $quotationCollectionFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        QuoteManagementInterface $b2bQuoteManagement
    ) {
        $this->_supplierFactory = $supplierFactory;
        $this->_helper = $helper;
        $this->_formKeyValidator = $formKeyValidator;
        $this->_resultJsonFactory = $resultJsonFactory;
        $this->quotationCollectionFactory = $quotationCollectionFactory;
        $this->b2bQuoteManagement = $b2bQuoteManagement;
        parent::__construct($context);
    }

    public function execute()
    {
        $helper = $this->_objectManager->create(\Webkul\Marketplace\Helper\Data::class);
        $isPartner = $helper->isSeller();
        if ($isPartner == 1) {
            try {
                if (!$this->_formKeyValidator->validate($this->getRequest())) {
                    $result["error"] = true;
                    $result["reload"] = true;
                    $resultJson = $this->_resultJsonFactory->create();
                    return $resultJson->setData($result);
                }
                $result = [];
                $result["error"] = false;
                $result["reload"] = false;
                if ($this->getRequest()->isPost()) {
                    if ($this->_helper->isUserLoggedIn() && $this->_helper->isSeller()) {
                        $type = \Webkul\B2BMarketplace\Helper\Quote::TYPE_SUPPLIER;
                        $receiverId = $this->getRequest()->getParam("ref");
                        $msg = $this->getRequest()->getParam("msg");
                        $senderId = $this->_helper->getSupplierId();

                        $msg = $this->_helper->sendMessage($msg, $senderId, $receiverId, $type);

                        // Save Attachments
                        $this->saveAttachment($msg, $type);
                        
                        // Update Supplier Sent Quote Status
                        $this->processSupplierQuotationStatus($senderId);

                        $result["error"] = false;
                        $result["reload"] = false;
                        $result["msg"] = $msg->getMsg();
                        $result['time'] = $this->_helper->formatDate(
                            $msg->getCreatedAt(),
                            \IntlDateFormatter::LONG,
                            true
                        );
                    } else {
                        $result["error"] = true;
                        $result["reload"] = true;
                    }
                } else {
                    $result["error"] = true;
                    $result["reload"] = true;
                }
            } catch (\Exception $e) {
                $result["error"] = true;
                $result["reload"] = true;
                $result["msg"] = $e->getMessage();
            }

            $resultJson = $this->_resultJsonFactory->create();
            return $resultJson->setData($result);
        } else {
            return $this->resultRedirectFactory->create()->setPath(
                'marketplace/account/becomeseller',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }

    public function saveAttachment($msg, $type)
    {
        if ($attachments = $this->getRequest()->getParam("attachments")) {
            $msgId = $msg->getId();
            foreach ($attachments as $key => $attachment) {
                $this->b2bQuoteManagement->saveMessageAttachment($attachment, $msgId, $type);
            }
        }
    }

    /**
     * processSupplierQuotationStatus() Method
     * Updating Supplier Sent Quote Status.
     *
     * @param int $senderId
     * @return void
     */
    private function processSupplierQuotationStatus($senderId)
    {
        try {
            $requestId = $this->getRequest()->getParam("request_id");
            $itemId = $this->getRequest()->getParam("item_id");
            if ($requestId && $itemId) {
                $status = \Webkul\B2BMarketplace\Helper\Quote::QUOTE_STATUS_ANSWERED;
                $customerStatus = \Webkul\B2BMarketplace\Helper\Quote::QUOTE_STATUS_PENDING;
                $collection = $this->quotationCollectionFactory->create()
                    ->addFieldToFilter("request_id", $requestId)
                    ->addFieldToFilter("quote_item_id", $itemId);
                foreach ($collection as $value) {
                    // Check If current supplier is correct
                    if ($value->getSellerId() == $senderId) {
                        $value->setStatus($status);
                        if ($customerStatus) {
                            $value->setCustomerStatus($customerStatus);
                        }
                        $value->save();
                    }
                }
            }
        } catch (\Exception $e) {
            $e->getMessage();
        }
    }
}
