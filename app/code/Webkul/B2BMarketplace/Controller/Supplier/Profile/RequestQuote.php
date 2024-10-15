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

namespace Webkul\B2BMarketplace\Controller\Supplier\Profile;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Webkul\B2BMarketplace\Helper\MpHelper as HelperData;

/**
 * Supplier RequestQuote controller.
 */
class RequestQuote extends \Magento\Customer\Controller\AbstractAccount
{
    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;

    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * @param Context     $context
     * @param PageFactory $resultPageFactory
     * @param HelperData $helperData
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        HelperData $helperData
    ) {
        $this->_resultPageFactory = $resultPageFactory;
        $this->helperData = $helperData;
        parent::__construct($context);
    }

    /**
     * Marketplace Seller's Profile Page.
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $helper = $this->helperData;
        if (!$helper->getSellerProfileDisplayFlag()) {
            $this->getRequest()->initForward();
            $this->getRequest()->setActionName('noroute');
            $this->getRequest()->setDispatched(false);

            return false;
        }
        $shopUrl = $helper->getRequestQuoteUrl();
        if (!$shopUrl) {
            $shopUrl = $this->getRequest()->getParam('shop');
        }
        if ($shopUrl) {
            $data = $helper->getSellerDataByShopUrl($shopUrl);
            if ($data->getSize()) {
                $resultPage = $this->_resultPageFactory->create();
                $resultPage->getConfig()->getTitle()->set(
                    __('Request a Quote')
                );

                return $resultPage;
            }
        }

        return $this->resultRedirectFactory->create()->setPath(
            'marketplace',
            ['_secure' => $this->getRequest()->isSecure()]
        );
    }
}
