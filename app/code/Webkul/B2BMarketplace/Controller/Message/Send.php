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

namespace Webkul\B2BMarketplace\Controller\Message;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Webkul\B2BMarketplace\Api\QuoteManagementInterface;

class Send extends Action
{
    /**
     * @var FormKeyValidator
     */
    private $_formKeyValidator;

    /**
     * @var QuoteManagementInterface
     */
    private $b2bQuoteManagement;

    /**
     * @param Context $context
     * @param \Webkul\B2BMarketplace\Model\SupplierFactory $supplierFactory
     * @param \Webkul\B2BMarketplace\Helper\Data $helper
     * @param \Webkul\B2BMarketplace\Helper\Quote $b2bQuoteHelper
     * @param FormKeyValidator $formKeyValidator
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param QuoteManagementInterface $b2bQuoteManagement
     */
    public function __construct(
        Context $context,
        \Webkul\B2BMarketplace\Model\SupplierFactory $supplierFactory,
        \Webkul\B2BMarketplace\Helper\Data $helper,
        \Webkul\B2BMarketplace\Helper\Quote $b2bQuoteHelper,
        FormKeyValidator $formKeyValidator,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        QuoteManagementInterface $b2bQuoteManagement
    ) {
        $this->_supplierFactory = $supplierFactory;
        $this->_helper = $helper;
        $this->b2bQuoteHelper = $b2bQuoteHelper;
        $this->_formKeyValidator = $formKeyValidator;
        $this->_resultJsonFactory = $resultJsonFactory;
        $this->b2bQuoteManagement = $b2bQuoteManagement;
        parent::__construct($context);
    }

    public function execute()
    {
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
                if ($this->_helper->isUserLoggedIn()) {
                    $type = $this->getRequest()->getParam("type");
                    $receiverId = $this->getRequest()->getParam("ref");
                    $msg = $this->getRequest()->getParam("msg");
                    $requestId = $this->getRequest()->getParam("request_id");
                    $itemId = $this->getRequest()->getParam("item_id");

                    if ($type == \Webkul\B2BMarketplace\Helper\Quote::TYPE_SUPPLIER) {
                        $senderId = $this->_helper->getSupplierId();
                    } else {
                        $senderId = $this->_helper->getCustomerId();
                    }

                    $msg = $this->_helper->sendMessage($msg, $senderId, $receiverId, $type);

                    // Save Attachments
                    if ($attachments = $this->getRequest()->getParam("attachments")) {
                        $msgId = $msg->getId();
                        foreach ($attachments as $key => $attachment) {
                            $this->b2bQuoteManagement->saveMessageAttachment($attachment, $msgId, $type);
                        }
                    }

                    if ($requestId && $itemId) {
                        if ($type == \Webkul\B2BMarketplace\Helper\Quote::TYPE_BUYER) {
                            $status = \Webkul\B2BMarketplace\Helper\Quote::QUOTE_STATUS_PENDING;
                            $customerStatus = \Webkul\B2BMarketplace\Helper\Quote::QUOTE_STATUS_ANSWERED;
                        } else {
                            $status = \Webkul\B2BMarketplace\Helper\Quote::QUOTE_STATUS_ANSWERED;
                            $customerStatus = \Webkul\B2BMarketplace\Helper\Quote::QUOTE_STATUS_PENDING;
                        }
                        $this->b2bQuoteHelper->setQuotationStatus($requestId, $itemId, $status, $customerStatus);
                    }
                    $result["error"] = false;
                    $result["reload"] = false;
                    $result["msg"] = $msg->getMsg();
                    $result["time"] = $this->_helper->formatDate($msg->getCreatedAt(), \IntlDateFormatter::LONG, true);
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
    }
}
