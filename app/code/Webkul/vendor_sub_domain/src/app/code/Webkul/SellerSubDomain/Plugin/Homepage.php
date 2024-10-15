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
namespace Webkul\SellerSubDomain\Plugin;

use Magento\Framework\App\Action\Action;

class Homepage
{
    /**
     * @var \Webkul\SellerSubDomain\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $_request;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @param \Webkul\SellerSubDomain\Helper\Data        $helper
     * @param \Magento\Framework\App\Request\Http        $request
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Webkul\SellerSubDomain\Helper\Data $helper,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->_helper = $helper;
        $this->_request = $request;
        $this->_storeManager = $storeManager;
    }

    public function afterDispatch(\Magento\Framework\App\Action\Action $dispatch, $resultPage)
    {
        $request = $this->_request;
        $storeId = $this->_storeManager->getStore()->getId();
        if (!$request->getPathInfo() && $this->_helper->checkShopExistsByCurrentUrl($storeId)) {
            $resultPage->addHandle('marketplace_seller_profile');
            $resultPage->getConfig()->getTitle()->set(
                __('Marketplace Seller Profile')
            );
            $data = $resultPage->getLayout()->getChildBlocks('main');
            foreach ($data as $key => $d) {
                $resultPage->getLayout()->unsetElement($key);
            }
            $notRemovableBlock = [
                "marketplace_sellerprofile",
                "formkey",
                "authentication-popup",
                "customer.section.config",
                "customer.customer.data",
                "customer.data.invalidation.rules",
                "pageCache",
                "marketplace_seller_top_block",
                "subdomain-minicart-update"
            ];
            $data = $resultPage->getLayout()->getChildBlocks('content');
            foreach ($data as $key => $d) {
                if (!in_array($key, $notRemovableBlock)) {
                    $resultPage->getLayout()->unsetElement($key);
                }
            }
            $resultPage->getLayout()->unsetElement('cms_static_block');

            return $resultPage;
        }
        return $resultPage;
    }
}
