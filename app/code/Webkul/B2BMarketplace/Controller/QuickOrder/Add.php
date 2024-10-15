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

namespace Webkul\B2BMarketplace\Controller\QuickOrder;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Checkout\Model\Cart as CustomerCart;
use Magento\Framework\Exception\NoSuchEntityException;

class Add extends \Magento\Checkout\Controller\Cart
{
    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    /**
     * @var \Magento\Checkout\Model\CompositeConfigProvider
     */
    protected $configProvider;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    private $serializer;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param CustomerCart $cart
     * @param ProductRepositoryInterface $productRepository
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\Checkout\Model\CompositeConfigProvider $configProvider
     * @param \Magento\Framework\Serialize\Serializer\Json|null $serializer
     *
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        CustomerCart $cart,
        ProductRepositoryInterface $productRepository,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Checkout\Model\CompositeConfigProvider $configProvider,
        \Magento\Framework\Serialize\Serializer\Json $serializer = null
    ) {
        parent::__construct(
            $context,
            $scopeConfig,
            $checkoutSession,
            $storeManager,
            $formKeyValidator,
            $cart
        );
        $this->productRepository = $productRepository;
        $this->jsonHelper = $jsonHelper;
        $this->configProvider = $configProvider;
        $this->serializer = $serializer ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Framework\Serialize\Serializer\Json::class);
    }

    /**
     * Initialize product instance from request data
     *
     * @return \Magento\Catalog\Model\Product|false
     */
    protected function _initProduct()
    {
        $productId = (int)$this->getRequest()->getParam('product');
        if ($productId) {
            $storeId = $this->_objectManager->get(
                \Magento\Store\Model\StoreManagerInterface::class
            )->getStore()->getId();
            try {
                return $this->productRepository->getById($productId, false, $storeId);
            } catch (NoSuchEntityException $e) {
                return false;
            }
        }
        return false;
    }

    /**
     * Add product to shopping cart action
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $params = $this->getRequest()->getParams();

        if (!$this->_formKeyValidator->validate($this->getRequest())) {
            if (isset($params['current_url']) && !empty($params['current_url'])) {
                return $params['current_url'];
            } else {
                return $this->resultRedirectFactory->create()->setPath('marketplace/');
            }
        }

        try {
            if (isset($params['qty'])) {
                $filter = new \Zend_Filter_LocalizedToNormalized(
                    ['locale' => $this->_objectManager->get(
                        \Magento\Framework\Locale\ResolverInterface::class
                    )->getLocale()]
                );
                $params['qty'] = $filter->filter($params['qty']);
            }

            $product = $this->_initProduct();
            $related = $this->getRequest()->getParam('related_product');

            /**
             * Check product availability
             */
            if (!$product) {
                $resultData['error'] = 1;
                $resultData['message'] = __("Product doesn't exist.");
            } elseif ($product && !$product->getIsSalable()) {
                $resultData['error'] = 1;
                $result['message'] = [
                    'statusText' => __('Out of stock')
                ];
            } else {
                $this->cart->addProduct($product, $params);
                if (!empty($related)) {
                    $this->cart->addProductsByIds(explode(',', $related));
                }

                $this->cart->save();

                /**
                 * @todo remove wishlist observer \Magento\Wishlist\Observer\AddToCart
                 */
                $this->_eventManager->dispatch(
                    'checkout_cart_add_product_complete',
                    ['product' => $product, 'request' => $this->getRequest(), 'response' => $this->getResponse()]
                );

                if (!$this->_checkoutSession->getNoCartRedirect(true)) {
                    if (!$this->cart->getQuote()->getHasError() || $this->_checkoutSession->getItemMinQtyStatus()) {
                        $message = __(
                            'You added %1 to your shopping cart.',
                            $product->getName()
                        );
                        if ($itemId = $this->_checkoutSession->getLastAddedItem()) {
                            $resultData['item'] = $itemId;
                            $resultData['price'] = $this->_checkoutSession->getLastAddedItemPrice();
                            $resultData['qty'] = $this->_checkoutSession->getLastAddedItemQty();
                        }
                        $resultData['error'] = 0;
                        $resultData['message'] =  $message;
                    } else {
                        $resultData['error'] = 1;
                        $resultData['message'] =  __('We can\'t add this item to your shopping cart right now.');
                    }
                }
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $resultData['error'] = 1;
            $resultData['message'] =  $this->_objectManager->get(
                \Magento\Framework\Escaper::class
            )->escapeHtml($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('We can\'t add this item to your shopping cart right now.'));
            $this->_objectManager->get(\Psr\Log\LoggerInterface::class)->critical($e);
            $resultData['error'] = 1;
            $resultData['message'] =  __('We can\'t add this item to your shopping cart right now.');
        }
        $resultData['checkout_data'] = $this->getSerializedCheckoutConfig();
        $this->getResponse()->representJson(
            $this->jsonHelper->jsonEncode($resultData)
        );
    }

    /**
     * Retrieve checkout configuration
     *
     * @return array
     *
     */
    public function getCheckoutConfig()
    {
        return $this->configProvider->getConfig();
    }

    /**
     * @return bool|string
     * @since 100.2.0
     */
    public function getSerializedCheckoutConfig()
    {
        return $this->serializer->serialize(
            $this->getCheckoutConfig()
        );
    }
}
