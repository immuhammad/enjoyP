<?php
/**
 * Webkul Affiliate Ads Banner.
 *
 * @category  Webkul
 * @package   Webkul_Affiliate
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Affiliate\Block\User;

use Magento\Framework\View\Element\Template\Context;
use Magento\Customer\Model\Session;
use Magento\Cms\Model\Template\FilterProvider;
use Webkul\Affiliate\Helper\Data as AffDataHelper;
use Webkul\Affiliate\Model\TextBannerFactory;

class Banner extends \Webkul\Affiliate\Block\User\UserAbstract
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    public $adslists;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var \Magento\Cms\Model\Template\FilterProvider
     */
    private $filterProvider;

    /**
     * @param Context           $context
     * @param Session           $customerSession,
     * @param FilterProvider   $filterProvider,
     * @param AffDataHelper     $affDataHelper,
     * @param CollectionFactory $collectionFactory,
     * @param array             $data
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        FilterProvider $filterProvider,
        AffDataHelper $affDataHelper,
        TextBannerFactory $collectionFactory,
        array $data = []
    ) {
    
        $this->collectionFactory = $collectionFactory;
        $this->filterProvider = $filterProvider;
        parent::__construct($context, $customerSession, $affDataHelper, $data);
    }

    /**
     * @return bool|\Magento\Ctalog\Model\ResourceModel\Product\Collection
     */
    public function getAllAds()
    {
        if (!($customerId = $this->getCustomerSession()->getCustomerId())) {
            return false;
        }

        if (!$this->adslists) {
            $this->adslists = $this->collectionFactory->create()->getCollection();
        }
        $param = $this->getRequest()->getParams();
        if (isset($param['bnTitle']) && $param['bnTitle']) {
            $bnTitle = $param['bnTitle'];
            $this->adslists->addFieldToFilter('title', ['like' => '%' . $bnTitle. '%']);
        }
        return $this->adslists;
    }

    /**
     * Get HTML code for each ads
     * @param Magento\Catalog\Model\Product $product
     * @return string
     */
    public function getHtmlCodeForAds($banner, $affId)
    {
        $size = explode('x', $banner->getBannerSize());
        if (count($size) == 1) {
            $size = [0 => 'auto', 1 => 'auto'];
        }
        $link = $banner->getLink()."?&aff_id=".$affId."&banner=".$banner->getEntityId();
        $bannerText = $this->filterProvider->getPageFilter()
                           ->filter(
                               str_replace(
                                   [
                                    "<script>",
                                    "</script>"
                                    ],
                                   [
                                        "&lt;script&gt;",
                                        "&lt;/script&gt;"
                                    ],
                                   $banner->getText()
                               )
                           );
        return "<div style='display: inline-block;padding: 10px; border: 1px solid #CCC;"
                    ." overflow:auto; box-sizing:content-box;width:"
                    .$size[1]."px;height:"
                    .$size[0]."px;'><a href='".$link."' style='text-decoration: none;'><strong>"
                    .$banner->getTitle()
                    ."</strong></a><br /><br /><a href='".$link."' style='text-decoration: none;'>"
                    .$bannerText."</a></div>";
    }
    
    /**
     * Get filtered content
     * @param string $saveText
     * @return string
     */
    public function getEditorContent($saveText)
    {
        return $this->filterProvider->getPageFilter()->filter($saveText);
    }
    /**
     * @return $this
     */
    public function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getAllAds()) {
            $pager = $this->getLayout()->createBlock(
                \Magento\Theme\Block\Html\Pager::class,
                'affiliate.ads.list.pager'
            )
                                            ->setCollection($this->getAllAds());
            $this->setChild('pager', $pager);
            $this->getAllAds()->load();
        }
        return $this;
    }

    /**
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }
}
