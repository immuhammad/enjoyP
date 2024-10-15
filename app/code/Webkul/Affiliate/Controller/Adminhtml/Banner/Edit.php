<?php
/**
 * Webkul Affiliate Add Banner
 * @category  Webkul
 * @package   Webkul_Affiliate
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
 
namespace Webkul\Affiliate\Controller\Adminhtml\Banner;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Edit extends \Magento\Backend\App\Action
{
   /**
    * @var \Magento\Framework\View\Result\PageFactory
    */
    private $resultPageFactory;

    /**
     * @param Context        $context,
     * @param PageFactory    $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * Create new product page
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Webkul_Affiliate::manager');
        $resultPage->getConfig()->getTitle()->prepend(__('Affiliate Banner'));
        $resultPage->addContent($resultPage->getLayout()
        ->createBlock(\Webkul\Affiliate\Block\Adminhtml\Banner\Edit::class));
        return $resultPage;
    }
}
