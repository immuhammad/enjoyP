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
use Magento\Framework\View\Result\PageFactory;
use Webkul\B2BMarketplace\Helper\Quote as B2bQuoteHelper;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;

class RequestPost extends \Magento\Customer\Controller\AbstractAccount
{
    /**
     * @var FormKeyValidator
     */
    protected $_formKeyValidator;

    /**
     * @param Context                                       $context
     * @param FormKeyValidator                              $formKeyValidator
     * @param PageFactory                                   $resultPageFactory
     * @param B2bQuoteHelper                                $b2bQuoteHelper
     * @param \Webkul\B2BMarketplace\Model\ItemFactory      $itemFactory
     * @param \Webkul\B2BMarketplace\Model\QuotationFactory $quotationFactory
     */
    public function __construct(
        Context $context,
        FormKeyValidator $formKeyValidator,
        PageFactory $resultPageFactory,
        B2bQuoteHelper $b2bQuoteHelper,
        \Webkul\B2BMarketplace\Model\ItemFactory $itemFactory,
        \Webkul\B2BMarketplace\Model\QuotationFactory $quotationFactory
    ) {
        parent::__construct($context);
        $this->_formKeyValidator = $formKeyValidator;
        $this->_resultPageFactory = $resultPageFactory;
        $this->_b2bQuoteHelper = $b2bQuoteHelper;
        $this->itemFactory = $itemFactory;
        $this->_quotationFactory = $quotationFactory;
    }

    public function execute()
    {
        $helper = $this->_objectManager->create(\Webkul\Marketplace\Helper\Data::class);
        $isPartner = $helper->isSeller();
        if ($isPartner == 1) {
            if ($this->getRequest()->getPost()) {
                try {
                    if (!$this->_formKeyValidator->validate($this->getRequest())) {
                        return $this->resultRedirectFactory->create()->setPath(
                            'b2bmarketplace/supplier/quotes',
                            ['_secure' => $this->getRequest()->isSecure()]
                        );
                    }
                    $quoteId = (int) $this->getRequest()->getPost("quote_id");
                    $itemId = (int) $this->getRequest()->getPost("quote_item_id");
                    if ($this->_b2bQuoteHelper->isValidSupplierToQuoteItem($quoteId, $itemId)) {
                        $sellerId = $this->_b2bQuoteHelper->getSupplierId();
                        $requestId = $this->_b2bQuoteHelper->generateQuoteRequestId($quoteId, $sellerId);
                        $data = $this->getRequest()->getParams();
                        $data["seller_id"] = $sellerId;
                        $data["request_id"] = $requestId;
                        $data["status"] = B2bQuoteHelper::QUOTE_STATUS_ANSWERED;
                        $data["customer_status"] = B2bQuoteHelper::QUOTE_STATUS_WAITING;
                        $data["currency_rate"] = $this->_b2bQuoteHelper->getCurrentCurrencyRate();
                        $data["price"] = $this->_b2bQuoteHelper->getBaseCurrencyPrice($data['price']);
                        if (isset($data['sample_charge_per_unit'])) {
                            $data["sample_charge_per_unit"] = $this->_b2bQuoteHelper->getBaseCurrencyPrice(
                                $data['sample_charge_per_unit']
                            );
                        }
                        $quotation = $this->_quotationFactory->create();
                        $quotation->setData($data)->save();
                        $conversation = $this->_objectManager->create("Webkul\B2BMarketplace\Model\Conversation");

                        $data = [];
                        $data['seller_quote_id'] = $quotation->getId();
                        $data['response'] = $this->getRequest()->getPost("note");
                        $data['item_id'] = $itemId;
                        $data['quote_status'] = 0;
                        $data['sender_type'] = 1;
                        $data['sender_id'] = $sellerId;
                        $data['receiver_id'] = $this->_b2bQuoteHelper->getQuote($quoteId)->getCustomerId();

                        $conversation->setData($data)->save();

                        $this->itemFactory->create()
                            ->load($itemId)
                            ->setId($itemId)
                            ->setStatus(B2bQuoteHelper::QUOTE_STATUS_PROCESSING)
                            ->save();

                        // sent quotation mail to customer
                        $sender = "Supplier";
                        $seller = $this->_b2bQuoteHelper->getCustomer($sellerId);
                        $senderName = $seller->getFirstname()." ".$seller->getLastname();
                        $senderEmail = $seller->getEmail();

                        $customer = $this->_b2bQuoteHelper->getCustomer($data['receiver_id']);
                        $customerName = $customer->getFirstname()." ".$customer->getLastname();
                        $receiverEmail = $customer->getEmail();

                        $emailTempVariables = [];
                        $emailTempVariables['myvar1'] = $customerName;
                        $emailTempVariables['myvar2'] = $this->_b2bQuoteHelper->getQuote($quoteId)->getQuoteTitle();
                        $emailTempVariables['myvar3'] = $this->getRequest()->getPost("qty");
                        $emailTempVariables['myvar4'] = $this->_b2bQuoteHelper->getFormattedPrice(
                            $this->getRequest()->getPost("price")
                        );
                        $emailTempVariables['requestId'] = $quotation->getId();
                        $emailTempVariables['isQuoteStatusUpdate'] = 0;

                        $senderDetails = [];
                        $senderDetails["name"] = $senderName;
                        $senderDetails["email"] = $senderEmail;
                
                        $receiverDetails = [];
                        $receiverDetails["name"] = $customerName;
                        $receiverDetails["email"] = $receiverEmail;
                
                        $this->_objectManager->create(
                            'Webkul\B2BMarketplace\Helper\Email'
                        )->rfqReplyToCustomerEmail($emailTempVariables, $senderDetails, $receiverDetails);
                        $this->messageManager->addSuccess(__('Quote successfully sent to customer.'));
                    } else {
                        $this->messageManager->addError(__('Invalid Request.'));
                    }
                } catch (\Exception $e) {
                    $this->messageManager->addError($e->getMessage());
                }
            } else {
                $this->messageManager->addError(__('Invalid Request.'));
            }

            return $this->resultRedirectFactory ->create()->setPath(
                'b2bmarketplace/supplier/quotes',
                ['_secure'=>$this->getRequest()->isSecure()]
            );
        } else {
            return $this->resultRedirectFactory->create()->setPath(
                'marketplace/account/becomeseller',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }
}
