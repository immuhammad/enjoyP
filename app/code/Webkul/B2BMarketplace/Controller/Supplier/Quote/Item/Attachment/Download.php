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

namespace Webkul\B2BMarketplace\Controller\Supplier\Quote\Item\Attachment;

use Webkul\B2BMarketplace\Helper\Quote as B2bQuoteHelper;

class Download extends \Magento\Framework\App\Action\Action
{
    /**
     * Quote Attachment Repository
     *
     * @var \Webkul\B2BMarketplace\Api\Data\ItemAttachmentInterfaceFactory
     */
    private $itemAttachmentRepository;

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
     * @param \Webkul\B2BMarketplace\Api\Data\ItemAttachmentInterfaceFactory $itemAttachmentRepository
     * @param \Webkul\B2BMarketplace\Model\Attachment\File $file
     * @param \Webkul\B2BMarketplace\Logger\Logger $logger
     * @param \Webkul\B2BMarketplace\Helper\Quote $b2bQuoteHelper
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Webkul\B2BMarketplace\Api\ItemAttachmentRepositoryInterface $itemAttachmentRepository,
        \Webkul\B2BMarketplace\Model\Attachment\File $file,
        \Webkul\B2BMarketplace\Logger\Logger $logger,
        \Webkul\B2BMarketplace\Helper\Quote $b2bQuoteHelper
    ) {
        parent::__construct($context);
        $this->itemAttachmentRepository = $itemAttachmentRepository;
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
        $quoteId = $this->getRequest()->getParam('quoteId');
        $attachmentId = $this->getRequest()->getParam('attachmentId');
        try {
            /** @var \Webkul\B2BMarketplace\Api\Data\ItemAttachmentInterfaceFactory $itemAttachment */
            $itemAttachment = $this->itemAttachmentRepository->get($attachmentId);
            $itemId = $itemAttachment->getQuoteItemId();

            if (!$this->b2bQuoteHelper->isValidSupplierQuoteItem($quoteId, $itemId)) {
                throw new \Magento\Framework\Exception\NotFoundException(__('Attachment not found.'));
            }

            if ($itemAttachment && $itemAttachment->getAttachmentId() === null) {
                throw new \Magento\Framework\Exception\NoSuchEntityException;
            }
            $this->file->downloadItemContents($itemAttachment);
        } catch (\Exception $e) {
            $this->logger->critical($e);
            throw new \Magento\Framework\Exception\NotFoundException(__('Attachment not found.'));
        }
    }
}
