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
namespace Webkul\SellerSubDomain\Helper;

use Magento\Framework\App\ActionInterface;

class Page extends \Magento\Cms\Helper\Page
{
    /**
     * @var \Data
     */
    protected $_helper;

    /**
     * @param \Magento\Framework\App\Helper\Context                $context
     * @param \Magento\Framework\Message\ManagerInterface          $messageManager
     * @param \Magento\Cms\Model\Page                              $page
     * @param \Magento\Framework\View\DesignInterface              $design
     * @param \Magento\Cms\Model\PageFactory                       $pageFactory
     * @param \Magento\Store\Model\StoreManagerInterface           $storeManager
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Framework\Escaper                           $escaper
     * @param \Magento\Framework\View\Result\PageFactory           $resultPageFactory
     * @param Data                                                 $helper
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Cms\Model\Page $page,
        \Magento\Framework\View\DesignInterface $design,
        \Magento\Cms\Model\PageFactory $pageFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\Escaper $escaper,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        Data $helper
    ) {
        $this->_helper = $helper;
        parent::__construct(
            $context,
            $messageManager,
            $page,
            $design,
            $pageFactory,
            $storeManager,
            $localeDate,
            $escaper,
            $resultPageFactory
        );
    }

    /**
     * Return result CMS page
     *
     * @param Action $action
     * @param null $pageId
     * @return \Magento\Framework\View\Result\Page|bool
     */
    public function prepareResultPage(ActionInterface $action, $pageId = null)
    {
        if ($pageId !== null && $pageId !== $this->_page->getId()) {
            $delimiterPosition = strrpos($pageId, '|');
            if ($delimiterPosition) {
                $pageId = substr($pageId, 0, $delimiterPosition);
            }

            $this->_page->setStoreId($this->_storeManager->getStore()->getId());
            if (!$this->_page->load($pageId)) {
                return false;
            }
        }

        $this->setPageData();

        if (!$this->_page->getId()) {
            return false;
        }

        $inRange = $this->_localeDate->isScopeDateInInterval(
            null,
            $this->_page->getCustomThemeFrom(),
            $this->_page->getCustomThemeTo()
        );

        if ($this->_page->getCustomTheme()) {
            if ($inRange) {
                $this->_design->setDesignTheme($this->_page->getCustomTheme());
            }
        }
        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $this->setLayoutType($inRange, $resultPage);

        $resultPage = $this->setHandle($resultPage);

        $resultPage->addPageLayoutHandles(['id' => $this->_page->getIdentifier()]);

        $this->_eventManager->dispatch(
            'cms_page_render',
            ['page' => $this->_page, 'controller_action' => $action, 'request' => $this->_getRequest()]
        );

        if ($this->_page->getCustomLayoutUpdateXml() && $inRange) {
            $layoutUpdate = $this->_page->getCustomLayoutUpdateXml();
        } else {
            $layoutUpdate = $this->_page->getLayoutUpdateXml();
        }
        if (!empty($layoutUpdate)) {
            $resultPage->getLayout()->getUpdate()->addUpdate($layoutUpdate);
        }

        $contentHeadingBlock = $resultPage->getLayout()->getBlock('page_content_heading');
        if ($contentHeadingBlock) {
            $contentHeading = $this->_escaper->escapeHtml($this->_page->getContentHeading());
            $contentHeadingBlock->setContentHeading($contentHeading);
        }

        $resultPage = $this->unsetElemet($resultPage);

        return $resultPage;
    }

    public function isSellerDomain()
    {
        return (
            $this->_helper->checkShopExistsByCurrentUrl(
                $this->_storeManager->getStore()->getId()
            ) &&
            $this->_page->getIdentifier() == $this->_helper->getCmsHomePageIdentifier()
        );
    }

    public function setHandle($resultPage)
    {
        if ($this->isSellerDomain()) {
            $resultPage->addHandle('marketplace_seller_profile');
            $resultPage->getConfig()->getTitle()->set(
                __('Marketplace Seller Profile')
            );
        } else {
            $resultPage->addHandle('cms_page_view');
        }
        return $resultPage;
    }

    public function unsetElemet($resultPage)
    {
        if ($this->isSellerDomain()) {
            $data = $resultPage->getLayout()->getChildBlocks('main');
            foreach ($data as $key => $d) {
                $resultPage->getLayout()->unsetElement($key);
            }
            $resultPage->getLayout()->unsetElement('cms_static_block');
        }
        return $resultPage;
    }

    public function setPageData()
    {
        if ($this->isSellerDomain()) {
            $this->_page->setTitle(__('Marketplace Seller Profile'));
            $this->_page->setContentHeading(__('Marketplace Seller Profile'));
            $this->_page->setContent('');
        }
    }
}
