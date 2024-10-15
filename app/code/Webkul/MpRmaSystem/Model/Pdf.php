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

namespace Webkul\MpRmaSystem\Model;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\App\Response\Http\FileFactory;

class Pdf
{
    /**
     * Initialize Depenedencies
     *
     * @param \Webkul\MpRmaSystem\Helper\Data $rmaHelper
     * @param \Webkul\MpRmaSystem\Model\DetailsFactory $details
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface
     * @param \Webkul\MpRmaSystem\Model\ReasonsRepository $reasonRepo
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     */
    public function __construct(
        \Webkul\MpRmaSystem\Helper\Data $rmaHelper,
        \Webkul\MpRmaSystem\Model\DetailsFactory $details,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface,
        \Webkul\MpRmaSystem\Model\ReasonsRepository $reasonRepo,
        DateTime $date,
        FileFactory $fileFactory
    ) {
        $this->rmaHelper                    = $rmaHelper;
        $this->details                      = $details;
        $this->_customerRepositoryInterface = $customerRepositoryInterface;
        $this->reasonRepo                   = $reasonRepo;
        $this->date                         = $date;
        $this->fileFactory                  = $fileFactory;
    }
    
    /**
     * Generate pdf
     *
     * @param int $rmaId
     * @return pdf
     */
    public function generatePdf($rmaId)
    {
        $rmaDetails = $this->details->create()->load($rmaId);
        $productDetails = $this->rmaHelper->getRmaProductDetails($rmaId);
        $customerId = $rmaDetails->getCustomerId();
        $customer = $this->_customerRepositoryInterface->getById($customerId);
        
        $pdf = new \Zend_Pdf();
        $pdf->pages[] = $pdf->newPage(\Zend_Pdf_Page::SIZE_A4);
        $page = $pdf->pages[0]; // this will get reference to the first page.
        $style = new \Zend_Pdf_Style();
        $style->setLineColor(new \Zend_Pdf_Color_Rgb(0, 0, 0));
        $font = \Zend_Pdf_Font::fontWithName(\Zend_Pdf_Font::FONT_TIMES);
        $style->setFont($font, 15);
        $page->setStyle($style);
        $width = $page->getWidth();
        $hight = $page->getHeight();
        $x = 30;
        $pageTopalign = 850; //default PDF page height
        $this->y = 850 - 100; //print table row from page top – 100px

        // set heading
        $this->insertHeading($page, $style, $font, $x);

        // Customer Details
        $this->insertCustomerDetails($page, $style, $font, $x, $customer);

        // Rma Details
        $this->insertRmaDetails($page, $style, $font, $x, $rmaDetails);
        
        // insert rma products
        $this->insertProducts($page, $style, $font, $x, $productDetails);

        $date = $this->date->date('Y-m-d_H-i-s');
        return $this->fileFactory->create(
            'rma' . $date . '.pdf',
            $pdf->render(),
            DirectoryList::VAR_DIR,
            'application/pdf'
        );
    }

    /**
     * Insert heading
     *
     * @param \Zend_Pdf $page
     * @param \Zend_Pdf_Style $style
     * @param \Zend_Pdf_Font $font
     * @param int $x
     * @return void
     */
    public function insertHeading($page, $style, $font, $x)
    {
        $style->setFont($font, 16);
        $page->setStyle($style);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0.7));
        $page->setLineColor(new \Zend_Pdf_Color_GrayScale(1));
        $page->drawRectangle(30, $this->y + 10, $page->getWidth()-30, $this->y +70);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $style->setFont($font, 20);
        $page->setStyle($style);
        $page->drawText(__("RMA Details"), $x + 170, $this->y+40, 'UTF-8');

        $this->y -= 90;
    }

    /**
     * Insert customer details
     *
     * @param \Zend_Pdf $page
     * @param \Zend_Pdf_Style $style
     * @param \Zend_Pdf_Font $font
     * @param int $x
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customer
     * @return void
     */
    public function insertCustomerDetails($page, $style, $font, $x, $customer)
    {
        $style->setFont($font, 16);
        $page->setStyle($style);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0.7));
        $page->drawRectangle(30, $this->y + 10, $page->getWidth()-30, $this->y +70);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $style->setFont($font, 15);
        $page->setStyle($style);
        $page->drawText(__("Cutomer Details"), $x + 5, $this->y+50, 'UTF-8');
        $style->setFont($font, 11);
        $page->setStyle($style);
        $page->drawText(__(
            "Name : %1",
            $customer->getFirstname().' '.$customer->getLastname()
        ), $x + 5, $this->y+33, 'UTF-8');
        $page->drawText(__("Email : %1", $customer->getEmail()), $x + 5, $this->y+16, 'UTF-8');

        $this->y -= 90;
    }

    /**
     * Insert rma details
     *
     * @param \Zend_Pdf $page
     * @param \Zend_Pdf_Style $style
     * @param \Zend_Pdf_Font $font
     * @param int $x
     * @param array $rmaDetails
     * @return void
     */
    public function insertRmaDetails($page, $style, $font, $x, $rmaDetails)
    {
        $status = $rmaDetails->getStatus();
        $finalStatus = $rmaDetails->getFinalStatus();
        $resolution = $this->rmaHelper->getResolutionTypeTitle($rmaDetails->getResolutionType());
        $rmaStatus = $this->rmaHelper->getRmaStatusTitle($status, $finalStatus);
        $style->setFont($font, 16);
        $page->setStyle($style);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0.7));
        $page->drawRectangle(30, $this->y - 20, $page->getWidth()-30, $this->y +70);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $style->setFont($font, 15);
        $page->setStyle($style);
        $page->drawText(__("RMA Details"), $x + 5, $this->y+50, 'UTF-8');
        $style->setFont($font, 11);
        $page->setStyle($style);
        $page->drawText(__("Order Id : %1", $rmaDetails->getOrderRef()), $x + 5, $this->y+32, 'UTF-8');
        $page->drawText(__("Rma Status : %1", $rmaStatus), $x + 5, $this->y+16, 'UTF-8');
        $page->drawText(__("Resolution Type : %1", $resolution), $x + 5, $this->y-1, 'UTF-8');
 
        $this->y -= 60;
    }

    /**
     * Insert rma products
     *
     * @param \Zend_Pdf $page
     * @param \Zend_Pdf_Style $style
     * @param \Zend_Pdf_Font $font
     * @param int $x
     * @param array $productDetails
     * @return void
     */
    public function insertProducts($page, $style, $font, $x, $productDetails)
    {
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0.5));
        $page->setLineColor(new \Zend_Pdf_Color_GrayScale(0.45));
        $page->drawRectangle(30, $this->y -20, $page->getWidth()-30, $this->y + 5);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $style->setFont($font, 12);
        $page->setStyle($style);
        $page->setLineColor(new \Zend_Pdf_Color_GrayScale(0));
        $page->drawText(__("S.NO"), $x + 5, $this->y-10, 'UTF-8');
        $page->drawText(__("Product Name"), $x + 40, $this->y-10, 'UTF-8');
        $page->drawText(__("Sku"), $x + 160, $this->y-10, 'UTF-8');
        $page->drawText(__("Price"), $x + 220, $this->y-10, 'UTF-8');
        $page->drawText(__("Qty"), $x + 280, $this->y-10, 'UTF-8');
        $page->drawText(__("Reason"), $x + 330, $this->y-10, 'UTF-8');
        $totalAmount = 0;
        $this->y -= 20;
        $i = 1;
        foreach ($productDetails as $product) {
            $reasonColl = $this->reasonRepo->getById($product->getReasonId());
            $style->setFont($font, 10);
            $page->setStyle($style);
            $add = 9;
            $page->drawText($product->getSku(), $x + 160, $this->y-30, 'UTF-8');
            $page->drawText($product->getPrice(), $x + 220, $this->y-30, 'UTF-8');
            $page->drawText($product->getQty(), $x + 285, $this->y-30, 'UTF-8');
            $page->drawText($reasonColl->getReason(), $x + 330, $this->y-30, 'UTF-8');
            $pro = $product->getName();
            $page->drawText($pro, $x + 40, $this->y-30, 'UTF-8');
            $page->drawText($i, $x + 5, $this->y-30, 'UTF-8');
            
            $this->y -= 35;
            $i++;
        }
        $this->y -= 150;
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0.5));
        $page->setLineColor(new \Zend_Pdf_Color_GrayScale(1));
        $page->drawRectangle(30, $this->y -62, $page->getWidth()-30, $this->y - 100);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));

        // total Amount
        $style->setFont($font, 15);
        $page->setStyle($style);
    }
}
