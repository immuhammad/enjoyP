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

namespace Webkul\B2BMarketplace\Controller\Supplier\Quote;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Webkul\B2BMarketplace\Helper\Quote as B2BQuoteHelper;
use Webkul\B2BMarketplace\Model\QuotationFactory;
use Webkul\B2BMarketplace\Model\ItemFactory;
use Webkul\B2BMarketplace\Model\ConversationFactory;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;

class Message extends Action
{
    /**
     * @var B2BQuoteHelper
     */
    private $quoteHelper;

    /**
     * @var QuotationFactory
     */
    private $quotationFactory;

    /**
     * @var ItemFactory
     */
    private $itemFactory;

    /**
     * @var ConversationFactory
     */
    private $conversationFactory;

    /**
     * @var FormKeyValidator
     */
    private $_formKeyValidator;

    /**
     * @param Context $context
     * @param \Webkul\B2BMarketplace\Model\SupplierFactory $supplierFactory
     * @param B2BQuoteHelper $quoteHelper
     * @param QuotationFactory $quotationFactory
     * @param ItemFactory $itemFactory
     * @param ConversationFactory $conversationFactory
     * @param FormKeyValidator $formKeyValidator
     */
    public function __construct(
        Context $context,
        \Webkul\B2BMarketplace\Model\SupplierFactory $supplierFactory,
        B2BQuoteHelper $quoteHelper,
        QuotationFactory $quotationFactory,
        ItemFactory $itemFactory,
        ConversationFactory $conversationFactory,
        FormKeyValidator $formKeyValidator,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        $this->_supplierFactory = $supplierFactory;
        $this->_quoteHelper = $quoteHelper;
        $this->quotationFactory = $quotationFactory;
        $this->itemFactory = $itemFactory;
        $this->conversationFactory = $conversationFactory;
        $this->_formKeyValidator = $formKeyValidator;
        $this->_resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $helper = $this->_objectManager->create(\Webkul\Marketplace\Helper\Data::class);
        $isPartner = $helper->isSeller();
        if ($isPartner == 1) {
            $result = [];
            $result["error"] = false;
            $result["reload"] = true;
            if ($this->getRequest()->isPost()) {
                try {
                    if (!$this->_formKeyValidator->validate($this->getRequest())) {
                        $result["error"] = true;
                        $result["reload"] = true;
                        $resultJson = $this->_resultJsonFactory->create();
                        return $resultJson->setData($result);
                    }
                    if ($this->_quoteHelper->isLoggedIn()) {
                        $supplierId = $this->_quoteHelper->getSupplierId();
                        $type = $this->getRequest()->getParam("type");
                        $id = $this->getRequest()->getParam("id");
                        $msg = $this->getRequest()->getParam("msg");
                        $quotation = $this->quotationFactory->create()->load($id);
                        $quote = $this->_quoteHelper->getQuoteByRequestId($quotation->getRequestId());
                        $customerId = $quote->getCustomerId();
                        if ($quotation->getId()) {
                            $data = [];
                            $data['seller_quote_id'] = $id;
                            $data['sender_type'] = B2BQuoteHelper::TYPE_SUPPLIER;
                            $data['item_id'] = $quotation->getQuoteItemId();
                            $data['receiver_id'] = $customerId;
                            $data['sender_id'] = $supplierId;
                            $data["created_at"] = date("Y-m-d h:i:sa");
                            $data["updated_at"] = date("Y-m-d h:i:sa");

                            // Update Sent Quote Status
                            $returnData = $this->updateQuoteStatus($type, $quotation, $data, $id);

                            $quotation = $returnData[0];
                            $quoteMsg = $returnData[1];
                            $data = $returnData[2];

                            $data['response'] = $msg;

                            $quotation->setId($id)->save();

                            // Save Message Data
                            $this->conversationFactory->create()->setData($data)->save();

                            $result["error"] = false;
                            // sent quotation mail to customer
                            $seller = $this->_quoteHelper->getCustomer($supplierId);
                            $sellerName = $seller->getFirstname()." ".$seller->getLastname();
                            $sellerEmail = $seller->getEmail();

                            $customer = $this->_quoteHelper->getCustomer($customerId);
                            $customerName = $customer->getFirstname()." ".$customer->getLastname();
                            $customerEmail = $customer->getEmail();

                            $emailTempVariables = [];
                            $emailTempVariables['myvar1'] = $customerName;
                            $emailTempVariables['myvar2'] = $quote->getQuoteTitle();
                            $emailTempVariables['myvar3'] = $quotation->getQty();
                            $emailTempVariables['myvar4'] = $this->_quoteHelper
                            ->getFormattedPrice($quotation->getPrice());
                            $emailTempVariables['isQuoteStatusUpdate'] = 1;
                            $emailTempVariables['requestId'] = $id;
                            $emailTempVariables['status'] = $quoteMsg;
                            $emailTempVariables['message'] = $msg;

                            $senderDetails = [];
                            $senderDetails["name"] = $sellerName;
                            $senderDetails["email"] = $sellerEmail;
                    
                            $receiverDetails = [];
                            $receiverDetails["name"] = $customerName;
                            $receiverDetails["email"] = $customerEmail;

                            $this->_objectManager->create(
                                'Webkul\B2BMarketplace\Helper\Email'
                            )->rfqReplyToCustomerEmail($emailTempVariables, $senderDetails, $receiverDetails);
                        } else {
                            $result["error"] = true;
                        }
                    } else {
                        $result["error"] = true;
                    }
                } catch (\Exception $e) {
                    $result["error"] = true;
                    $result["msg"] = $e->getMessage();
                }
            } else {
                $result["error"] = true;
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
    
    public function updateQuoteStatus($type, $quotation, $data, $id)
    {
        if ($type == 1) {
            $quotation->addData(
                [
                    "status" => B2BQuoteHelper::QUOTE_STATUS_APPROVED,
                    "customer_status" => B2BQuoteHelper::QUOTE_STATUS_APPROVED
                ]
            );
            $data['quote_status'] = 1;
            $this->itemFactory->create()
                ->load($id)
                ->setId($id)
                ->setStatus(B2BQuoteHelper::QUOTE_STATUS_APPROVED)
                ->save();
            $quoteMsg = 'Approved';
        } elseif ($type == 2) {
            $quotation->addData(
                [
                    "status" => B2BQuoteHelper::QUOTE_STATUS_REJECTED,
                    "customer_status" => B2BQuoteHelper::QUOTE_STATUS_REJECTED
                ]
            );
            $data['quote_status'] = 2;
            $quoteMsg = 'Rejected';
        } else {
            $savedStatus = $quotation->getStatus();
            if ($savedStatus != B2BQuoteHelper::QUOTE_STATUS_APPROVED &&
            $savedStatus != B2BQuoteHelper::QUOTE_STATUS_REJECTED) {
                $quotation->addData(
                    [
                        "status" => B2BQuoteHelper::QUOTE_STATUS_ANSWERED,
                        "customer_status" => B2BQuoteHelper::QUOTE_STATUS_PENDING
                    ]
                );
                $quoteMsg = 'Pending';
            } else {
                $quoteMsg = $this->_quoteHelper->getLabel($savedStatus);
            }
        }
        $returnData[0] = $quotation;
        $returnData[1] = $quoteMsg;
        $returnData[2] = $data;
        return $returnData;
    }
}
