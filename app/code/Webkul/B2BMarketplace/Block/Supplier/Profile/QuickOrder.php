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

namespace Webkul\B2BMarketplace\Block\Supplier\Profile;

use \Magento\Customer\Model\Customer;
use \Magento\Customer\Model\Session;
use \Webkul\Marketplace\Helper\Data as MpHelper;
use \Webkul\Marketplace\Model\FeedbackFactory;
use \Webkul\Marketplace\Model\ResourceModel\Product\CollectionFactory;
use \Webkul\Marketplace\Model\ProductFactory as MpProductModel;
use \Magento\Catalog\Model\ProductFactory;
use \Magento\Checkout\Model\Session as CheckoutSession;

class QuickOrder extends \Webkul\Marketplace\Block\Profile
{
    private $request;

    private $b2bMpHelper;

    protected $mpHelper;

    private $formatter;

    private $checkoutSession;

    private $_objectManager;

    protected $helperFactory;

    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Data\Helper\PostHelper $postDataHelper,
        \Magento\Framework\Url\Helper\Data $urlHelper,
        Customer $customer,
        Session $session,
        \Magento\Framework\Stdlib\StringUtils $stringUtils,
        MpHelper $mpHelper,
        FeedbackFactory $feedbackModel,
        CollectionFactory $mpProductCollection,
        MpProductModel $mpProductModel,
        ProductFactory $productFactory,
        \Magento\Framework\App\RequestInterface $request,
        \Webkul\B2BMarketplace\Helper\MpHelper $b2bMpHelper,
        \Magento\Framework\Locale\Format $formatter,
        CheckoutSession $checkoutSession,
        \Magento\Framework\ObjectManagerInterface $helperFactory,
        \Webkul\Marketplace\Model\ResourceModel\SellerFlagReason\CollectionFactory $reasonCollection,
        \Magento\Cms\Helper\Wysiwyg\Images $wysiwygImages = null,
        array $data = []
    ) {
        $this->request = $request;
        $this->b2bMpHelper = $b2bMpHelper;
        $this->mpHelper = $mpHelper;
        $this->formatter = $formatter;
        $this->checkoutSession = $checkoutSession;
        $this->helperFactory = $helperFactory;
        $this->wysiwygImages = $wysiwygImages ?: \Magento\Framework\App\ObjectManager::getInstance()
        ->create(\Magento\Cms\Helper\Wysiwyg\Images::class);
        $this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        parent::__construct(
            $context,
            $postDataHelper,
            $urlHelper,
            $customer,
            $session,
            $stringUtils,
            $mpHelper,
            $feedbackModel,
            $mpProductCollection,
            $mpProductModel,
            $productFactory,
            $data,
            $reasonCollection
        );
    }
    /**
     * @return array
     */
    public function getProfileDetail($value = '')
    {
        $shopUrl =  $this->b2bMpHelper->getQuickOrderUrl();
        if (!$shopUrl) {
            $shopUrl = $this->request->getParam('shop');
        }
        if ($shopUrl) {
            $data = $this->mpHelper->getSellerCollectionObjByShop($shopUrl);
            foreach ($data as $seller) {
                return $seller;
            }
        }
    }

    public function getPriceFormat()
    {
        return $this->formatter->getPriceFormat();
    }

    public function getQuote()
    {
        return $this->checkoutSession->getQuote();
    }

    public function isQuoteExist()
    {
        $isQuoteExist = 0;
        if ($this->getQuote()->getId()) {
            $isQuoteExist = 1;
        }
        return $isQuoteExist;
    }

    public function getCartItems()
    {
        $cartItems = [];
        $index = 0;
        if ($this->getQuote()->getId()) {
            $this->getQuote()->collectTotals();
            $items = $this->getQuote()->getAllVisibleItems();
            foreach ($items as $key => $item) {
                $index++;
                $cartItems[$index]['item_id'] = $item->getId();
                $cartItems[$index]['product_id'] = $item->getProductId();
                $cartItems[$index]['name'] = $item->getName();
                $cartItems[$index]['qty'] = $item->getQty();
                $cartItems[$index]['price'] = $item->getPrice();
                $cartItems[$index]['row_total'] = $item->getRowTotal();
            }
        }
        return $cartItems;
    }

    /**
     * getRequestProductForQuote
     *
     * @param int $supplierId
     * @return \Magento\Catalog\Api\Data\ProductInterface||null
     */
    public function getRequestProductForQuickOrder($supplierId)
    {
        if ($productId = $this->getRequest()->getParam('product')) {
            try {
                // Get current product by Product Id
                $product = $this->_objectManager->create(
                    \Magento\Catalog\Api\ProductRepositoryInterface::class
                )->getById($productId);
                // Check if current product is of correct supplier
                $productSupplierId = 0;
                $marketplaceProduct = $this->_objectManager->create(
                    \Webkul\Marketplace\Helper\Data::class
                )->getSellerProductDataByProductId($productId);
                foreach ($marketplaceProduct as $value) {
                    $productSupplierId = $value['seller_id'];
                }
                if ($productSupplierId == $supplierId) {
                    return $product;
                } else {
                    return null;
                }
            } catch (\Exception $e) {
                return null;
            }
        } else {
            return null;
        }
    }

    /**
     * @param String $className
     * @return object
     */
    public function helper($className)
    {
        $helper = $this->helperFactory->get($className);
        if (false === $helper instanceof \Magento\Framework\App\Helper\AbstractHelper) {
            throw new \LogicException($className . ' doesn\'t extends Magento\Framework\App\Helper\AbstractHelper');
        }
        return $helper;
    }

    public function getWysiwygUrl()
    {
        $currentTreePath = $this->wysiwygImages->idEncode(
            \Magento\Cms\Model\Wysiwyg\Config::IMAGE_DIRECTORY
        );
        $url =  $this->getUrl(
            'marketplace/wysiwyg_images/index',
            [
                'current_tree_path' => $currentTreePath
            ]
        );
        return $url;
    }

    public function getQueryParamsForSuppQuote()
    {
        return $this->getRequest()->getParams();
    }
}
