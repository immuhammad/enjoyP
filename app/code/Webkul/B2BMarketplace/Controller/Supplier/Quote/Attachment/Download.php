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

namespace Webkul\B2BMarketplace\Controller\Supplier\Quote\Attachment;

use Webkul\B2BMarketplace\Helper\Quote as B2bQuoteHelper;

class Download extends \Magento\Framework\App\Action\Action
{
    /**
     * Quote Attachment Repository
     *
     * @var \Webkul\B2BMarketplace\Api\Data\QuoteAttachmentInterfaceFactory
     */
    private $quoteAttachmentRepository;

    /**
     * File
     *
     * @var \Webkul\B2BMarketplace\Logger\Logger
     */
    private $file;

    /**
     * Logger
     *
     * @var \Webkul\B2BMarketplace\Logger\Logger
     */
    private $logger;

    /**
     * Quote Helper
     *
     * @var \Webkul\B2BMarketplace\Helper\Quote
     */
    private $b2bQuoteHelper;

    /**
     * Download constructor
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Webkul\B2BMarketplace\Api\Data\QuoteAttachmentInterfaceFactory $quoteAttachmentRepository
     * @param \Webkul\B2BMarketplace\Model\Attachment\File $file
     * @param \Webkul\B2BMarketplace\Logger\Logger $logger
     * @param \Webkul\B2BMarketplace\Helper\Quote $b2bQuoteHelper
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Webkul\B2BMarketplace\Api\QuoteAttachmentRepositoryInterface $quoteAttachmentRepository,
        \Webkul\B2BMarketplace\Model\Attachment\File $file,
        \Webkul\B2BMarketplace\Logger\Logger $logger,
        \Webkul\B2BMarketplace\Helper\Quote $b2bQuoteHelper
    ) {
        parent::__construct($context);
        $this->quoteAttachmentRepository = $quoteAttachmentRepository;
        $this->file = $file;
        $this->logger = $logger;
        $this->b2bQuoteHelper = $b2bQuoteHelper;
    }

    /**
     * Execute
     *
     * @return void
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        $attachmentId = $this->getRequest()->getParam('attachmentId');
        try {
            /** @var \Webkul\B2BMarketplace\Api\Data\QuoteAttachmentInterfaceFactory $quoteAttachment */
            $quoteAttachment = $this->quoteAttachmentRepository->get($attachmentId);

            if (!$this->b2bQuoteHelper->isValidQuote($quoteAttachment->getQuoteId(), B2bQuoteHelper::TYPE_SUPPLIER)) {
                throw new \Magento\Framework\Exception\NotFoundException(__('Attachment not found.'));
            }

            if ($quoteAttachment && $quoteAttachment->getAttachmentId() === null) {
                throw new \Magento\Framework\Exception\NoSuchEntityException;
            }
            $this->file->downloadContents($quoteAttachment);
        } catch (\Exception $e) {
            $this->logger->critical($e);
            throw new \Magento\Framework\Exception\NotFoundException(__('Attachment not found.'));
        }
    }
}
