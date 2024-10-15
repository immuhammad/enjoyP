<?php
/**
 * Save quote at customer end.
 *
 * @category  Webkul
 * @package   Webkul_Mpquotesystem
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Mpquotesystem\Controller\Adminhtml\Buyerquote;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\LayoutFactory;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Registry;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable\Variations\Prices;

class Productoptions extends \Magento\Framework\App\Action\Action
{

    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $_catalogProduct;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $_jsonHelper;

    /**
     * @var \Magento\Framework\View\Result\LayoutFactory
     */
    protected $resultLayoutFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var Registry
     */
    protected $_coreRegistry = null;

    /**
     * Product option
     *
     * @param LayoutFactory                                             $resultLayoutFactory
     * @param Context                                                   $context
     * @param ProductFactory                                            $catalogProduct
     * @param \Magento\Framework\Json\Helper\Data                       $jsonHelper
     * @param \Magento\Framework\Controller\Result\JsonFactory          $resultJsonFactory
     * @param Prices                                                    $variationPrices
     * @param ProductRepositoryInterface                                $productRepository
     * @param \Magento\Store\Model\StoreManagerInterface                $storeManager
     * @param Registry                                                  $coreRegistry
     */
    public function __construct(
        LayoutFactory $resultLayoutFactory,
        Context $context,
        ProductFactory $catalogProduct,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        Prices $variationPrices = null,
        ProductRepositoryInterface $productRepository,
        StoreManagerInterface $storeManager,
        Registry $coreRegistry
    ) {
        $this->resultLayoutFactory = $resultLayoutFactory;
        $this->_catalogProduct = $catalogProduct;
        $this->_jsonHelper = $jsonHelper;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->variationPrices = $variationPrices ?: ObjectManager::getInstance()->get(
            Prices::class
        );
        $this->productRepository = $productRepository;
        $this->_storeManager = $storeManager;
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context);
    }

    /**
     * Product option action
     */
    public function execute()
    {
        $params = $this->getRequest()->getParams();
        $productId = $params['id'];
        $currentStoreId = $this->_storeManager->getStore()->getId();
        $product = $this->productRepository->getById($productId, false, $currentStoreId);
        $this->_coreRegistry->register('current_product', $product);
        $this->_coreRegistry->register('product', $product);
        $resultLayout = $this->resultLayoutFactory->create();
        return $resultLayout;
    }
}
