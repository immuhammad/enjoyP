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

namespace Webkul\B2BMarketplace\Controller\Supplier\Attachment;

use Webkul\B2BMarketplace\Helper\Quote as B2bQuoteHelper;

class Download extends \Magento\Framework\App\Action\Action
{
    /**
     * /message Attachment Repository
     *
     * @var \Webkul\B2BMarketplace\Api\Data\AttachmentInterfaceFactory
     */
    private $attachmentRepository;

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
     * @var \Webkul\B2BMarketplace\Model\MessageFactory
     */
    private $messageFactory;

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
     * @param \Webkul\B2BMarketplace\Api\Data\AttachmentInterfaceFactory $attachmentRepository
     * @param \Webkul\B2BMarketplace\Model\Attachment\File $file
     * @param \Webkul\B2BMarketplace\Logger\Logger $logger
     * @param \Webkul\B2BMarketplace\Model\MessageFactory $messageFactory
     * @param \Webkul\B2BMarketplace\Helper\Quote $b2bQuoteHelper
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Webkul\B2BMarketplace\Api\AttachmentRepositoryInterface $attachmentRepository,
        \Webkul\B2BMarketplace\Model\Attachment\File $file,
        \Webkul\B2BMarketplace\Logger\Logger $logger,
        \Webkul\B2BMarketplace\Model\MessageFactory $messageFactory,
        \Webkul\B2BMarketplace\Helper\Quote $b2bQuoteHelper
    ) {
        parent::__construct($context);
        $this->attachmentRepository = $attachmentRepository;
        $this->file = $file;
        $this->logger = $logger;
        $this->messageFactory = $messageFactory;
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
            /** @var \Webkul\B2BMarketplace\Api\Data\AttachmentInterfaceFactory $attachment */
            $attachment = $this->attachmentRepository->get($attachmentId);

            if (!$this->isValidSupplierMessage($attachment->getMsgId())) {
                throw new \Magento\Framework\Exception\NotFoundException(__('Attachment not found.'));
            }

            if ($attachment && $attachment->getId() === null) {
                throw new \Magento\Framework\Exception\NoSuchEntityException;
            }
            $this->file->downloadAttachmentContents($attachment);
        } catch (\Exception $e) {
            $this->logger->critical($e);
            throw new \Magento\Framework\Exception\NotFoundException(__('Attachment not found.'));
        }
    }

    /**
     * isValidSupplierMessage
     *
     * @param int
     * @return bool
     */
    private function isValidSupplierMessage($id)
    {
        $model = $this->messageFactory->create()->load($id);

        $supplierId = $this->b2bQuoteHelper->getSupplierId();

        if ($model) {
            if ($model->getSenderId() == $supplierId || $model->getReceiverId() == $supplierId) {
                return true;
            }
        }
        return false;
    }
}
