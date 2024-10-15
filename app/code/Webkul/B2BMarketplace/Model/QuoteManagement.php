<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_B2BMarketplace
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\B2BMarketplace\Model;

use Webkul\B2BMarketplace\Api\Data\QuoteInterfaceFactory;
use Webkul\B2BMarketplace\Api\Data\ItemInterfaceFactory;
use Webkul\B2BMarketplace\Api\Data\QuoteAttachmentInterfaceFactory;
use Webkul\B2BMarketplace\Api\Data\ItemAttachmentInterfaceFactory;
use Webkul\B2BMarketplace\Api\Data\AttachmentInterfaceFactory;
use Webkul\B2BMarketplace\Api\QuoteManagementInterface;
use Webkul\B2BMarketplace\Api\QuoteRepositoryInterface;
use Webkul\B2BMarketplace\Api\QuoteAttachmentRepositoryInterface;
use Webkul\B2BMarketplace\Api\ItemAttachmentRepositoryInterface;
use Webkul\B2BMarketplace\Api\AttachmentRepositoryInterface;
use Webkul\B2BMarketplace\Helper\Quote as HelperQuote;
use Webkul\B2BMarketplace\Helper\Email as HelperEmail;
use Magento\Framework\Escaper;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;

/**
 * Class for managing b2b quotes.
 */
class QuoteManagement implements QuoteManagementInterface
{
    /**#@+
     * Quote attachments folder.
     */
    const ATTACHMENTS_FOLDER = 'supplier\files';
    /**#@-*/

    /**#@+
     * Quote item attachments folder.
     */
    const ITEM_ATTACHMENTS_FOLDER = 'tmp\catalog\product';
    /**#@-*/

    /**
     * @var QuoteInterfaceFactory
     */
    private $quoteFactory;

    /**
     * @var ItemInterfaceFactory
     */
    private $quoteItemFactory;

    /**
     * Quote Attachment factory
     *
     * @var QuoteAttachmentInterfaceFactory
     */
    private $quoteAttachmentFactory;

    /**
     * Quote Item Attachment factory
     *
     * @var ItemAttachmentInterfaceFactory
     */
    private $itemAttachmentFactory;

    /**
     * Message Attachment factory
     *
     * @var AttachmentInterfaceFactory
     */
    private $attachmentFactory;

    /**
     * @var QuoteRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var QuoteAttachmentRepositoryInterface
     */
    private $quoteAttachmentRepository;

    /**
     * @var ItemAttachmentRepositoryInterface
     */
    private $itemAttachmentRepository;

    /**
     * @var AttachmentRepositoryInterface
     */
    private $attachmentRepository;

    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * @var FilterBuilder
     */
    private $filterBuilder;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var HelperQuote
     */
    private $helperQuote;

    /**
     * @var HelperEmail
     */
    private $helperEmail;

    /**
     * @param QuoteInterfaceFactory $quoteFactory
     * @param ItemInterfaceFactory $quoteItemFactory
     * @param QuoteAttachmentInterfaceFactory $quoteAttachmentFactory
     * @param ItemAttachmentInterfaceFactory $itemAttachmentFactory
     * @param AttachmentInterfaceFactory $attachmentFactory
     * @param QuoteRepositoryInterface $quoteRepository
     * @param QuoteAttachmentRepositoryInterface $quoteAttachmentRepository
     * @param ItemAttachmentRepositoryInterface $itemAttachmentRepository
     * @param AttachmentRepositoryInterface $attachmentRepository
     * @param Escaper $escaper
     * @param FilterBuilder $filterBuilder
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param HelperQuote $helperQuote
     * @param HelperEmail $helperEmail
     */
    public function __construct(
        QuoteInterfaceFactory $quoteFactory,
        ItemInterfaceFactory $quoteItemFactory,
        QuoteAttachmentInterfaceFactory $quoteAttachmentFactory,
        ItemAttachmentInterfaceFactory $itemAttachmentFactory,
        AttachmentInterfaceFactory $attachmentFactory,
        QuoteRepositoryInterface $quoteRepository,
        QuoteAttachmentRepositoryInterface $quoteAttachmentRepository,
        ItemAttachmentRepositoryInterface $itemAttachmentRepository,
        AttachmentRepositoryInterface $attachmentRepository,
        Escaper $escaper,
        FilterBuilder $filterBuilder,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        HelperQuote $helperQuote,
        HelperEmail $helperEmail
    ) {
        $this->quoteFactory = $quoteFactory;
        $this->quoteItemFactory = $quoteItemFactory;
        $this->quoteAttachmentFactory = $quoteAttachmentFactory;
        $this->itemAttachmentFactory = $itemAttachmentFactory;
        $this->attachmentFactory = $attachmentFactory;
        $this->quoteRepository = $quoteRepository;
        $this->quoteAttachmentRepository = $quoteAttachmentRepository;
        $this->itemAttachmentRepository = $itemAttachmentRepository;
        $this->attachmentRepository = $attachmentRepository;
        $this->escaper = $escaper;
        $this->filterBuilder = $filterBuilder;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->helperQuote = $helperQuote;
        $this->helperEmail = $helperEmail;
    }

    /**
     * {@inheritdoc}
     */
    public function saveQuote(
        array $data = []
    ) {
        $data = $this->getRequestData($data);

        /** @var \Webkul\B2BMarketplace\Api\Data\QuoteInterface $quote */
        $quoteObject = $this->quoteFactory->create();
        $quoteObject->setData($data);
        $quote = $this->quoteRepository->save($quoteObject);
        $quoteId = $quote->getId();

        // Save Quote Attachments
        if (isset($data['quote_attachments']) && !empty($data['quote_attachments'])) {
            foreach ($data['quote_attachments'] as $file) {
                $this->saveQuoteAttachment($file, $quoteId);
            }
        }
        $supplierId = $data['supplier_id'];
        $customerId = $data['customer_id'];
        $rfqProducts = $data['rfq_product'];

        $itemIds = [];
        $itemsHtml = '';
        foreach ($rfqProducts as $rfqProduct) {
            if (isset($rfqProduct['name']) && $rfqProduct['name']) {
                if (isset($data["is_buying_lead"]) && $data["is_buying_lead"]) {
                    $rfqProduct['buying_lead_status'] = $data["is_buying_lead"];
                }
                $itemIds[] = $this->saveQuoteItem($rfqProduct, $supplierId, $quoteId);
    
                $itemsHtml .= '<h4>';
                $itemsHtml .= __(
                    'Requested Item : %1, Qty: %2, Price Per Qty: %3',
                    $rfqProduct['name'],
                    $rfqProduct['qty'],
                    $this->helperQuote->getFormattedPrice($rfqProduct['price'])
                );
                $itemsHtml .= '</h4>';
            }
        }
        if (isset($data["is_buying_lead"]) && $data["is_buying_lead"]) {
            $this->helperQuote->generateBuyingLead($itemIds);
        }

        // Send RFQ Mail to Supplier
        $this->processRFQMailToSupplier($quote, $itemsHtml, $supplierId, $customerId);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function saveQuoteItem($rfqProduct, $supplierId, $quoteId)
    {
        $sampleImages = [];
        $productId = $rfqProduct['product_id'];
        $data = [];
        if (array_key_exists('category', $rfqProduct)) {
            $data["categories"] = $this->escaper->escapeHtml($rfqProduct['category']);
        }
        $data["description"] = $this->escaper->escapeHtml($rfqProduct['description']);
        $data["name"] = $this->escaper->escapeHtml($rfqProduct['name']);
        $data["qty"] = $rfqProduct['qty'];
        $data["currency_rate"] = $this->helperQuote->getCurrentCurrencyRate();
        $data["price"] = $this->helperQuote->getBaseCurrencyPrice($rfqProduct['price']);
        $data["quote_id"] = $quoteId;
        $data["product_id"] = $productId;
        $data["seller_id"] = $supplierId;
        if (isset($rfqProduct['product_sample_images'])) {
            $sampleImages = $rfqProduct['product_sample_images'];
            $data["samples"] = $rfqProduct['product_sample_images'];
        } else {
            $data["samples"] = [];
        }
        $data["has_samples"] = 0;
        if ($rfqProduct['is_sample'] == "required") {
            $data["has_samples"] = 1;
        }
        if (isset($rfqProduct['buying_lead_status']) && $rfqProduct["buying_lead_status"]) {
            $data["buying_lead_status"] = $rfqProduct['buying_lead_status'];
        }
        $data["currency_rate"] = $this->helperQuote->getCurrentCurrencyRate();

        /** @var \Webkul\B2BMarketplace\Api\Data\QuoteItemInterface $quoteItem */
        $quoteItem = $this->quoteItemFactory->create();
        $quoteItem->setData($data)->save();
        $itemId = $quoteItem->getId();

        // Save Quote Item Attachments
        if (!empty($sampleImages)) {
            foreach ($sampleImages as $file) {
                $this->saveQuoteItemAttachment($file, $itemId);
            }
        }

        return $itemId;
    }

    /**
     * {@inheritdoc}
     */
    public function saveQuoteAttachment($data, $quoteId)
    {
        if (isset($data['name']) && isset($data['file']) && $quoteId) {
            /** @var \Webkul\B2BMarketplace\Api\Data\QuoteAttachmentInterface $quoteAttachment */
            $attachment = $this->quoteAttachmentFactory->create();
            $attachment->setQuoteId($quoteId)
                ->setFileName($data['name'])
                ->setFilePath($data['file']);
            $this->quoteAttachmentRepository->save($attachment);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function saveQuoteItemAttachment($data, $itemId)
    {
        if (isset($data['name']) && isset($data['file']) && $itemId) {
            /** @var \Webkul\B2BMarketplace\Api\Data\ItemAttachmentInterface $itemAttachment */
            $attachment = $this->itemAttachmentFactory->create();
            $attachment->setQuoteItemId($itemId)
                ->setFileName($data['name'])
                ->setFilePath($data['file']);
            $this->itemAttachmentRepository->save($attachment);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function saveMessageAttachment($data, $messageId, $type = 2)
    {
        if (isset($data['name']) && isset($data['file']) && $messageId) {
            /** @var \Webkul\B2BMarketplace\Api\Data\AttachmentInterface $attachment */
            $attachment = $this->attachmentFactory->create();
            $attachment->setMsgId($messageId)
                ->setType($type)
                ->setFileName($data['name'])
                ->setValue($data['file']);
            $this->attachmentRepository->save($attachment);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getQuoteAttachments($quoteId)
    {
        try {
            $filter =  $this->filterBuilder->setField('quote_id')
                ->setValue($quoteId)
                ->setConditionType('eq')
                ->create();
            $attachmentData = (array)($this->quoteAttachmentRepository->getList(
                $this->searchCriteriaBuilder->addFilters([$filter])->create()
            )->getItems());
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return [];
        }
        return $attachmentData;
    }

    /**
     * {@inheritdoc}
     */
    public function getQuoteItemAttachments($itemId)
    {
        try {
            $filter =  $this->filterBuilder->setField('quote_item_id')
                ->setValue($itemId)
                ->setConditionType('eq')
                ->create();
            $attachmentData = (array)($this->itemAttachmentRepository->getList(
                $this->searchCriteriaBuilder->addFilters([$filter])->create()
            )->getItems());
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return [];
        }
        return $attachmentData;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttachments($messageId)
    {
        try {
            $filter =  $this->filterBuilder->setField('msg_id')
                ->setValue($messageId)
                ->setConditionType('eq')
                ->create();
            $attachmentData = (array)($this->attachmentRepository->getList(
                $this->searchCriteriaBuilder->addFilters([$filter])->create()
            )->getItems());
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return [];
        }
        return $attachmentData;
    }

    /**
     * {@inheritdoc}
     */
    public function getRequestData($data)
    {
        if (isset($data['quote_attachments'])) {
            $data["quote_attachments"] = $data['quote_attachments'];
        } else {
            $data["quote_attachments"] = [];
        }

        if (isset($data['quote_title'])) {
            $data["quote_title"] = $this->escaper->escapeHtml($data['quote_title']);
        } else {
            $data["quote_title"] = '';
        }

        if (isset($data['quote_description'])) {
            $data["quote_desc"] = $this->escaper->escapeHtml($data['quote_description']);
        } else {
            $data["quote_desc"] = '';
        }

        if (isset($data['customer_name'])) {
            $data["customer_name"] = $this->escaper->escapeHtml($data['customer_name']);
        } else {
            $data["customer_name"] = '';
        }

        if (isset($data['customer_company_name'])) {
            $data["customer_company_name"] = $this->escaper->escapeHtml($data['customer_company_name']);
        } else {
            $data["customer_company_name"] = '';
        }

        if (isset($data['customer_address'])) {
            $data["customer_address"] = $this->escaper->escapeHtml($data['customer_address']);
        } else {
            $data["customer_address"] = '';
        }

        if (isset($data['customer_contact_no'])) {
            $data["customer_contact_no"] = $this->escaper->escapeHtml($data['customer_contact_no']);
        } else {
            $data["customer_contact_no"] = '';
        }

        if (isset($data['forward_quote'])) {
            $data["forward_quote"] = 1;
        } else {
            $data["forward_quote"] = 0;
        }

        if (isset($data['rfq_product']) && is_array($data['rfq_product'])) {
            $data["rfq_product"] = $data['rfq_product'];
        } else {
            $data["rfq_product"] = [];
        }

        return $data;
    }

    public function processRFQMailToSupplier($quote, $itemsHtml, $supplierId, $customerId)
    {
        $seller = $this->helperQuote->getCustomer($supplierId);
        $sellerName = $seller->getFirstname()." ".$seller->getLastname();
        $sellerEmail = $seller->getEmail();

        $customer = $this->helperQuote->getCustomer($customerId);
        $customerName = $customer->getFirstname()." ".$customer->getLastname();
        $customerEmail = $customer->getEmail();

        $emailTempVariables = [];
        $emailTempVariables['myvar1'] = $sellerName;
        $emailTempVariables['myvar2'] = $quote->getQuoteTitle();
        $emailTempVariables['myvar3'] = $quote->getQuoteDesc();
        $emailTempVariables['myvar4'] = $itemsHtml;

        $senderDetails = [];
        $senderDetails["name"] = $customerName;
        $senderDetails["email"] = $customerEmail;

        $receiverDetails = [];
        $receiverDetails["name"] = $sellerName;
        $receiverDetails["email"] = $sellerEmail;

        $this->helperEmail->rfqSupplierEmail(
            $emailTempVariables,
            $senderDetails,
            $receiverDetails
        );
    }
}
