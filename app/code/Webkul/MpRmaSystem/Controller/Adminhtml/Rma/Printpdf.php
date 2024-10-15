<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpRmaSystem
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpRmaSystem\Controller\Adminhtml\Rma;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\App\Response\Http\FileFactory;

class Printpdf extends \Magento\Framework\App\Action\Action
{

    /**
     * Initialize Dependencies
     *
     * @param Context $context
     * @param \Webkul\MpRmaSystem\Model\Pdf $pdf
     * @param \Psr\Log\LoggerInterface $logger
     * @return void
     */
    public function __construct(
        Context $context,
        \Webkul\MpRmaSystem\Model\Pdf $pdf,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->pdf    = $pdf;
        $this->logger = $logger;
        parent::__construct($context);
    }
    
    /**
     * Print Pdf Action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $rmaId = $this->getRequest()->getParam('rma_id');
        try {
            $this->pdf->generatePdf($rmaId);
        } catch (\Exception $e) {
            $this->logger->info($e->getMessage());
        }
    }
}
