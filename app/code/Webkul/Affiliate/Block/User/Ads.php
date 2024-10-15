<?php
/**
 * Webkul Affiliate Banner.
 *
 * @category  Webkul
 * @package   Webkul_Affiliate
 * @author    Webkul
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Affiliate\Block\User;

use Magento\Framework\View\Element\Template\Context;
use Magento\Customer\Model\Session;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Webkul\Affiliate\Helper\Data as AffDataHelper;
use Magento\Catalog\Helper\Image as ImageHelper;

class Ads extends \Webkul\Affiliate\Block\User\UserAbstract
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    private $productlists;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var \Magento\Catalog\Helper\Image
     */
    private $imageHelper;

    /**
     * @param Context           $context
     * @param Session           $customerSession,
     * @param AffDataHelper     $affDataHelper,
     * @param CollectionFactory $collectionFactory,
     * @param array             $data
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        AffDataHelper $affDataHelper,
        CollectionFactory $collectionFactory,
        ImageHelper $imageHelper,
        array $data = []
    ) {
    
        $this->collectionFactory = $collectionFactory;
        $this->imageHelper = $imageHelper;
        parent::__construct($context, $customerSession, $affDataHelper, $data);
    }

    /**
     * @return bool|\Magento\Ctalog\Model\ResourceModel\Product\Collection
     */
    public function getAllProducts()
    {
        if (!($customerId = $this->getCustomerSession()->getCustomerId())) {
            return false;
        }

        if (!$this->productlists) {
            $this->productlists = $this->collectionFactory->create()
                                        ->addFieldToFilter("status", 1)
                                        ->addAttributeToSelect('*')
                                        ->addFieldToFilter('visibility', ['neq'=>1]);
            ;
        }
        $param = $this->getRequest()->getParams();
        if (isset($param['proName']) && $param['proName']) {
            $proName = $param['proName'];
            $this->productlists->addFieldToFilter('name', ['like' => '%' . $proName. '%']);
        }
        return $this->productlists;
    }

    /**
     * getProImgUrl
     * @param Magento\Catalog\Model\Product $product
     * @return string
     */
    public function getProImgUrl($products)
    {
        return $this->imageHelper->init($products, 'product_page_image_small')
                                    ->setImageFile($products->getFile())->getUrl();
    }

    /**
     * getHtmlCodeForAds
     * @param Magento\Catalog\Model\Product $product
     * @return string
     */
    public function getHtmlCodeForAds($baseUrl, $product, $affId)
    {
        return "<div style='display:inline-block; padding: 10px; border: 1px solid #CCC;width:12%; '>
        <a href='".$baseUrl."catalog/product/view/id/".$product->getId()
                ."?&aff_id=".$affId."' style='text-decoration: none;'><div style='text-align:center;'><strong>"
                .$product->getName()."</strong></div><img src='"
                .$this->getProImgUrl($product)."' alt='Ad' /></a></div>";
    }
    
    /**
     * @return $this
     */
    public function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getAllProducts()) {
            $pager = $this->getLayout()
                    ->createBlock(
                        \Magento\Theme\Block\Html\Pager::class,
                        'affiliate.product.list.pager'
                    )
                    ->setCollection($this->getAllProducts());
            $this->setChild('pager', $pager);
            $this->getAllProducts()->load();
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
