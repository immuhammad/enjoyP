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

namespace Webkul\B2BMarketplace\Controller\Quote;

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
                    $customerId = $this->_quoteHelper->getCustomerId();
                    $type = $this->getRequest()->getParam("type");
                    $id = $this->getRequest()->getParam("id");
                    $msg = $this->getRequest()->getParam("msg");
                    $quotation = $this->quotationFactory->create()->load($id);
                    $quote = $this->_quoteHelper->getQuoteByRequestId($quotation->getRequestId());
                    $quoteItemId = $this->getRequest()->getParam("quoteItemId");
                    if ($quotation->getId()) {
                        $data = [];
                        $data['seller_quote_id'] = $id;
                        $data['item_id'] = $quotation->getQuoteItemId();
                        $data['receiver_id'] = $quotation->getSellerId();
                        $data['sender_id'] = $customerId;
                        $data["created_at"] = date("Y-m-d h:i:sa");
                        $data["updated_at"] = date("Y-m-d h:i:sa");
                        
                        // Update Sent Quote Status
                        if ($type == 1) {
                            $quotation->addData(
                                [
                                    "status" => B2BQuoteHelper::QUOTE_STATUS_APPROVED,
                                    "customer_status" => B2BQuoteHelper::QUOTE_STATUS_APPROVED
                                ]
                            );
                            $data['quote_status'] = 1;
                            $this->itemFactory->create()
                                ->load($quoteItemId)
                                ->setId($quoteItemId)
                                ->setStatus(B2BQuoteHelper::QUOTE_STATUS_APPROVED)
                                ->save();
                            $quoteMsg = __('Approved');
                        } elseif ($type == 2) {
                            $quotation->addData(
                                [
                                    "status" => B2BQuoteHelper::QUOTE_STATUS_REJECTED,
                                    "customer_status" => B2BQuoteHelper::QUOTE_STATUS_REJECTED
                                ]
                            );
                            $data['quote_status'] = 2;
                            $this->itemFactory->create()
                            ->load($quoteItemId)
                            ->setId($quoteItemId)
                            ->setStatus(B2BQuoteHelper::QUOTE_STATUS_REJECTED)
                            ->save();
                            $quoteMsg = __('Rejected');
                        } else {
                            $savedStatus = $quotation->getStatus();
                            $quoteMessage = $this->getQuoteMessage($savedStatus, $quotation);
                            $quotation = $quoteMessage[0];
                            $quoteMsg = $quoteMessage[1];
                        }
                        $data['response'] = $msg;

                        $quotation->setId($id)->save();

                        // Save Message Data
                        $this->conversationFactory->create()->setData($data)->save();

                        $result["error"] = false;
                        // sent quotation mail to supplier
                        $seller = $this->_quoteHelper->getCustomer($quotation->getSellerId());
                        $sellerName = $seller->getFirstname()." ".$seller->getLastname();
                        $sellerEmail = $seller->getEmail();

                        $customer = $this->_quoteHelper->getCustomer($customerId);
                        $customerName = $customer->getFirstname()." ".$customer->getLastname();
                        $customerEmail = $customer->getEmail();

                        $emailTempVariables = [];
                        $emailTempVariables['myvar1'] = $sellerName;
                        $emailTempVariables['myvar2'] = $quote->getQuoteTitle();
                        $emailTempVariables['myvar3'] = $msg;
                        $emailTempVariables['myvar4'] = $quoteMsg;
                        $emailTempVariables['requestId'] = $id;
                        $emailTempVariables['isQuoteStatusUpdate'] = 1;

                        $senderDetails = [];
                        $senderDetails["name"] = $customerName;
                        $senderDetails["email"] = $customerEmail;
                
                        $receiverDetails = [];
                        $receiverDetails["name"] = $sellerName;
                        $receiverDetails["email"] = $sellerEmail;

                        $this->_objectManager->create(
                            'Webkul\B2BMarketplace\Helper\Email'
                        )->rfqReplyToSupplierEmail($emailTempVariables, $senderDetails, $receiverDetails);
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
    }
    
    public function getQuoteMessage($savedStatus, $quotation)
    {
        if ($savedStatus != B2BQuoteHelper::QUOTE_STATUS_APPROVED &&
        $savedStatus != B2BQuoteHelper::QUOTE_STATUS_REJECTED) {
            $quotation->addData(
                [
                    "status" => B2BQuoteHelper::QUOTE_STATUS_PENDING,
                    "customer_status" => B2BQuoteHelper::QUOTE_STATUS_ANSWERED
                ]
            );
            $quoteMsg = 'Pending';
        } else {
            $quoteMsg = $this->_quoteHelper->getLabel($savedStatus);
        }
        $returnData[0] = $quotation;
        $returnData[1] = $quoteMsg;
        return $returnData;
    }
}
