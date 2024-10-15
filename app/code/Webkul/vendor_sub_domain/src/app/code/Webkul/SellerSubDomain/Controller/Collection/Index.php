<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_SellerSubDomain
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\SellerSubDomain\Controller\Collection;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

/**
 * Marketplace Seller Collection controller.
 */
class Index extends Action
{
    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;

    /**
     * @param Context                             $context
     * @param PageFactory                         $resultPageFactory
     * @param \Webkul\SellerSubDomain\Helper\Data $data
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Webkul\SellerSubDomain\Helper\Data $data
    ) {
        $this->_resultPageFactory = $resultPageFactory;
        $this->_helper = $data;
        parent::__construct($context);
    }

    /**
     * Marketplace Seller's Product Collection Page.
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $shop = $this->_helper->getShopNameByCurrentUrl();
        if ($shop) {
            $resultPage = $this->_resultPageFactory->create();
            $resultPage->addHandle('marketplace_seller_collection');
            $resultPage->getConfig()->getTitle()->set(
                __('Marketplace Seller Collection')
            );
            return $resultPage;
        }
        return $this->resultRedirectFactory->create()->setPath(
            'marketplace',
            ['_secure' => $this->getRequest()->isSecure()]
        );
    }
}
