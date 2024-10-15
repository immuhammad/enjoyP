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

namespace Webkul\B2BMarketplace\Controller\Supplier;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Webkul\B2BMarketplace\Helper\Quote as B2bQuoteHelper;

class Quote extends \Magento\Customer\Controller\AbstractAccount
{
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        B2bQuoteHelper $b2bQuoteHelper
    ) {
        parent::__construct($context);
        $this->_resultPageFactory = $resultPageFactory;
        $this->_b2bQuoteHelper = $b2bQuoteHelper;
    }

    public function execute()
    {
        $helper = $this->_objectManager->create(\Webkul\Marketplace\Helper\Data::class);
        $isPartner = $helper->isSeller();
        if ($isPartner == 1) {
            $quoteId = (int) $this->getRequest()->getParam("id");
            if ($quoteId) {
                if ($this->_b2bQuoteHelper->isValidQuote($quoteId, B2bQuoteHelper::TYPE_SUPPLIER)) {
                    $resultPage = $this->_resultPageFactory->create();
                    $resultPage->getConfig()->getTitle()->set(__('View Quote'));
                    return $resultPage;
                } else {
                    $this->messageManager->addError(__('Quote Does Not exists.'));
                }
            } else {
                $this->messageManager->addError(__('Quote Does Not exists.'));
            }
            return $this->resultRedirectFactory
                ->create()->setPath(
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
