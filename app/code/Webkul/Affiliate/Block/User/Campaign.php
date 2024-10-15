<?php
/**
 * Webkul Affiliate Campaign.
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

class Campaign extends \Webkul\Affiliate\Block\User\UserAbstract
{
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
    
    public function getSaveAction()
    {
        return $this->getUrl('affiliate/user/campaign', ['_secure' => $this->getRequest()->isSecure()]);
    }

    public function getAllDetail($affId)
    {
        $products = $this->collectionFactory->create()->addAttributeToSelect('*');

        $string='';
        $i=1;
        foreach ($products as $product) {
            $string=$string.
                        "<div class='showdata' style='overflow:hidden' id = 'showmain".$i."' data='"
                            .$product->getProductUrl()."?&aff_id=".$affId."'>".
                            "<div ><img  style='height:100px;' src='".$this->getProImgUrl($product)
                            ."' alt='Ad'><div  title='".$product->getName()."'
                             class='emailcampaign-product-name'>".$product->getName().
                            "</div></div></div>";
                $i++;
        }
        return $string;
    }

    public function getProImgUrl($products)
    {
        return $this->imageHelper->init($products, 'product_page_image_small')
                                  ->setImageFile($products->getFile())->getUrl();
    }
}
