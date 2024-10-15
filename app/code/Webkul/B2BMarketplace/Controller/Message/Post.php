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
use Webkul\B2BMarketplace\Api\QuoteManagementInterface;

/**
 * Post action for new message request from customer
 */
class Post extends Action
{
    /**
     * @var QuoteManagementInterface
     */
    private $b2bQuoteManagement;

    /**
     * @param Context $context
     * @param \Webkul\B2BMarketplace\Model\SupplierFactory $supplierFactory
     * @param \Webkul\Marketplace\Helper\Data $mpHelper
     * @param \Webkul\B2BMarketplace\Helper\Data $helper
     * @param \Webkul\B2BMarketplace\Helper\Quote $b2bQuoteHelper
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param QuoteManagementInterface $b2bQuoteManagement
     */
    public function __construct(
        Context $context,
        \Webkul\B2BMarketplace\Model\SupplierFactory $supplierFactory,
        \Webkul\Marketplace\Helper\Data $mpHelper,
        \Webkul\B2BMarketplace\Helper\Data $helper,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        QuoteManagementInterface $b2bQuoteManagement
    ) {
        $this->_supplierFactory = $supplierFactory;
        $this->_mpHelper = $mpHelper;
        $this->_helper = $helper;
        $this->_resultJsonFactory = $resultJsonFactory;
        $this->b2bQuoteManagement = $b2bQuoteManagement;
        parent::__construct($context);
    }

    public function execute()
    {
        try {
            $result = [];
            $result["error"] = false;
            $result["msg"] = "";
            $result["reload"] = false;
            $result["name"] = "";
            if ($this->getRequest()->isPost()) {
                if ($this->_mpHelper->isCustomerLoggedIn()) {
                    $type = \Webkul\B2BMarketplace\Helper\Quote::TYPE_BUYER;
                    $receiverId = $this->getRequest()->getParam("seller_id");
                    $msg = $this->getRequest()->getParam("msg");
                    $senderId = $this->_helper->getCustomerId();

                    $msg = $this->_helper->sendMessage($msg, $senderId, $receiverId, $type);

                    // Save Attachments
                    if ($attachments = $this->getRequest()->getParam("attachments")) {
                        $msgId = $msg->getId();
                        foreach ($attachments as $key => $attachment) {
                            $this->b2bQuoteManagement->saveMessageAttachment($attachment, $msgId, $type);
                        }
                    }
                    $result["error"] = false;
                    $result["msg"] = "success";
                    $result["reload"] = false;
                    $result["msg"] = $msg->getMsg();
                    $result["time"] = $this->_helper->formatDate($msg->getCreatedAt(), \IntlDateFormatter::LONG, true);
                } else {
                    $result["error"] = true;
                    $result["msg"] = __("Please login to send message to supplier.");
                    $result["reload"] = true;
                }
            } else {
                $result["error"] = true;
                $result["msg"] = __("Invalid request.");
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
