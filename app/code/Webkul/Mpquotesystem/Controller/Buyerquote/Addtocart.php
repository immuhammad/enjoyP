<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_Mpquotesystem
 * @author    Webkul
 * @copyright Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Mpquotesystem\Controller\Buyerquote;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\RequestInterface;
use Webkul\Mpquotesystem\Model\QuotesFactory;
use Webkul\Mpquotesystem\Helper\Data;
use Magento\Checkout\Model\Cart;
use Magento\Checkout\Model\Session;
use Magento\Customer\CustomerData\SectionPool;

class Addtocart extends \Magento\Customer\Controller\AbstractAccount
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;

    /**
     * @var QuotesFactory
     */
    protected $_quotesFactory;

    /**
     * @var Helper\Data
     */
    protected $_helper;

    /**
     * @var Magento\Checkout\Model\Cart
     */
    protected $_cartModel;

    /**
     * @var Checkout/Session
     */
    protected $_checkoutSession;

    /**
     * @var _sectionPool
     */
    protected $_sectionPool;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $_jsonHelper;

    /**
     * @param Context                                      $context
     * @param PageFactory                                  $resultPageFactory
     * @param \Magento\Customer\Model\Session              $customerSession
     * @param QuotesFactory                                $quotesFactory
     * @param Data                                         $helper
     * @param cart                                         $cartModel
     * @param \Magento\Framework\Json\Helper\Data          $jsonHelper
     * @param \Magento\Checkout\Model\Session              $checkoutSession
     * @param SectionPool                                  $sectionPool
     * @param \Magento\Framework\Serialize\Serializer\Json $serializer
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Magento\Customer\Model\Session $customerSession,
        QuotesFactory $quotesFactory,
        Data $helper,
        cart $cartModel,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Checkout\Model\Session $checkoutSession,
        SectionPool $sectionPool,
        \Magento\Framework\Serialize\Serializer\Json $serializer
    ) {
        $this->_customerSession = $customerSession;
        $this->_resultPageFactory = $resultPageFactory;
        $this->_quotesFactory = $quotesFactory;
        $this->_helper = $helper;
        $this->_cartModel = $cartModel;
        $this->_checkoutSession = $checkoutSession;
        $this->_sectionPool = $sectionPool;
        $this->_jsonHelper = $jsonHelper;
        $this->_serializer = $serializer;
        parent::__construct($context);
    }
    
    /**
     * Default customer account page.
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $result = [];
        $data = $this->getRequest()->getParams();
        $quoteId = 0;
        if (array_key_exists('quote_id', $data)) {
            $quoteId = $data['quote_id'];
        }
        if (!$quoteId) {
            $result['error'] = 1;
            $result['message'] = __('Quote does not exists, please contact to admin.');
            return $this->getResponse()->representJson(
                $this->_jsonHelper->jsonEncode($result)
            );
        }

        $quote = $this->_quotesFactory->create()->load($data['quote_id']);
        $quoteProductId = $quote->getProductId();
        if ($this->quoteProductAlreadyInCart($quoteProductId)) {
            $result['error'] = 1;
            $result['message'] = __('Same Product is already added to cart.');
            return $this->getResponse()->representJson(
                $this->_jsonHelper->jsonEncode($result)
            );
        }
        $customerId = $this->_customerSession->getCustomerId();
        $session = $this->_checkoutSession;
        if ($this->checkQuoteAlreadyAddedOrNot($session, $quoteProductId)) {
            $result['error'] = 1;
            $result['message'] = __('A Quote item of same product is already added in cart.');
            return $this->getResponse()->representJson(
                $this->_jsonHelper->jsonEncode($result)
            );
        }
        $productAddToCart = $this->_helper->getProduct($quote->getProductId());
        $params = [];
        $optionToAdd = [];

        // add attachment in quote
        $this->checkAttachementInQuote($quote, $productAddToCart);

        if (in_array(
            $productAddToCart->getTypeId(),
            ['simple', 'virtual', 'downloadable', 'configurable']
        )
        ) {
            //creating custom options to add
            $savedOptions = $this->_helper->convertStringAccToVersion(
                $quote->getProductOption(),
                'decode'
            );
            if (is_array($savedOptions) && !empty($savedOptions)) {
                foreach ($savedOptions as $key => $value) {
                    $optionToAdd[$key] = $value;
                }
            }
        }
        $params = $this->getParamsAccToProductType($productAddToCart, $quote, $optionToAdd);
        try {
            $cart = $this->_cartModel;
            $cart->addProduct($productAddToCart, $params);
            $cart->save();
            $result['error'] = 0;
            $configSetting = $this->_helper->getRedirectConfigSetting();
            if ($configSetting==0) {
                $result['redirecturl'] = '';
            } else {
                $result['redirecturl'] = $this->_url->getUrl('checkout/cart');
            }
            $result['message'] = __('Quote Product Is added In cart');
            return $this->getResponse()->representJson(
                $this->_jsonHelper->jsonEncode($result)
            );
        } catch (\Exception $e) {
            $result['error'] = 1;
            $result['message'] = __($e->getMessage());
            return $this->getResponse()->representJson(
                $this->_jsonHelper->jsonEncode($result)
            );
        }
    }

    /**
     * Get Params According To Product Type
     *
     * @param object $productAddToCart
     * @param object $quote
     * @param array  $optionToAdd
     *
     * @return void
     */
    public function getParamsAccToProductType($productAddToCart, $quote, $optionToAdd)
    {
        $data = $this->getRequest()->getParams();
        if ($productAddToCart->getTypeId() == 'configurable') {
            $superAttribute = $this->_helper->convertStringAccToVersion(
                $quote->getSuperAttribute(),
                'decode'
            );
            $params = [
                'product' => $quote->getProductId(),
                'qty' => $quote->getQuoteQty(),
                'super_attribute' => $superAttribute,
                'options' => $optionToAdd,
                'quote_id' => $data["quote_id"]
            ];
        } elseif ($productAddToCart->getTypeId() == 'bundle') {
            $bundleOptionArray = $this->_helper->convertStringAccToVersion(
                $quote->getBundleOption(),
                'decode'
            );
            $bundleOption = $bundleOptionArray['bundle_option'];
            $bundleOptionQty = $bundleOptionArray['bundle_option_qty'];
            $params = [
                'product' => $quote->getProductId(),
                'qty' => $quote->getQuoteQty(),
                'bundle_option' => $bundleOption,
                'bundle_option_qty' => $bundleOptionQty,
                'quote_id' => $data["quote_id"]
            ];
        } elseif ($productAddToCart->getTypeId() == 'downloadable') {
            $params = [
                'product' => $quote->getProductId(),
                'qty' => $quote->getQuoteQty(),
                'options' => $optionToAdd,
                'links' => $this->_helper->convertStringAccToVersion(
                    $quote->getLinks(),
                    'decode'
                ),
                'quote_id' => $data["quote_id"]
            ];
        } elseif (in_array($productAddToCart->getTypeId(), ['simple', 'virtual'])) {
            $params = [
                'product' => $quote->getProductId(),
                'qty' => $quote->getQuoteQty(),
                'options' => $optionToAdd,
                'quote_id' => $data["quote_id"]
            ];
        }
        return $params;
    }

    /**
     * Provide data to go back button
     *
     * @param string $backUrl
     * @return object
     */
    protected function goBack($backUrl = null)
    {
        $redirectUrl = $this->_url->getUrl('customer/account');
        if (!$this->getRequest()->isAjax()) {
            if ($backUrl || $backUrl = $redirectUrl) {
                $resultRedirect = $this->resultRedirectFactory->create();
                $resultRedirect->setUrl($backUrl);
                return $resultRedirect;
            }
        }
        $result = [];
        if ($backUrl || $backUrl = $redirectUrl) {
            $result['backUrl'] = $backUrl;
        }
        $this->getResponse()->representJson(
            $this->_jsonHelper->jsonEncode($result)
        );
    }

    /**
     * CheckQuoteAlreadyAddedOrNot check wwther a quote product already added in cart or not
     *
     * @param checkoutSession $session
     * @param int             $quoteProductId
     */
    public function checkQuoteAlreadyAddedOrNot($session, $quoteProductId)
    {
        foreach ($session->getQuote()->getAllItems() as $item) {
            if ($quoteProductId == $item->getProductId()) {
                if ($item->getParentItemId() === null && $item->getItemId() > 0) {
                    $quoteCollection = $this->_helper->getWkQuoteModel()->getCollection()
                        ->addFieldToFilter("item_id", $item->getItemId())
                        ->addFieldToFilter("product_id", $item->getProductId());
                    if ($quoteCollection->getSize()) {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    /**
     * QuoteProductAlreadyInCart
     *
     * @param int $quoteProductId
     * @return bool
     */
    public function quoteProductAlreadyInCart($quoteProductId)
    {
        $cartModel = $this->_cartModel;
        $cartitems = $cartModel->getItems();
        foreach ($cartitems as $item) {
            if ($quoteProductId == $item->getProductId()) {
                return true;
            }
        }
        return false;
    }

    /**
     * CheckAttachementInQuote
     *
     * @param object $quote
     * @param object $productAddToCart
     * @return void
     */
    public function checkAttachementInQuote($quote, $productAddToCart)
    {
        if ($quote->getAttachment()) {
            $attachmentUrl = $this->_helper->getAttachFullUrl($quote->getAttachment());
            
            $attachmentOption[] = [
                'label'     =>  __('Attachment'),
                'value'     =>  "<a href=\"".$attachmentUrl."\" target=\"_blank\">" . __('View Attachment') . "</a>"
            ];
            $additionalOptions = $this->_serializer->serialize($attachmentOption);
            $productAddToCart->addCustomOption('additional_options', $additionalOptions);
        }
    }
}
