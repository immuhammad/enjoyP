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

namespace Webkul\B2BMarketplace\Controller\Product;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Catalog\Model\ProductFactory;
use Webkul\Marketplace\Helper\Data as HelperData;

class Options extends Action
{
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_product;

    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_registry = null;

    /**
     * @param Context $context
     * @param ProductFactory $product
     * @param HelperData $helperData
     */
    public function __construct(
        Context $context,
        ProductFactory $product,
        HelperData $helperData,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->_product = $product;
        $this->helperData = $helperData;
        $this->resultPageFactory = $resultPageFactory;
        $this->_registry = $registry;
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        try {
            $requestedData = $this->getRequest()->getParams();
            $responseData = [];
            if (isset($requestedData['product']) && !empty($requestedData['product'])) {
                $resultPage = $this->resultPageFactory->create();
                $blockInstance = $resultPage->getLayout()->getBlock('supplier_product_options');
                $productModel = $this->_product->create()->load($requestedData['product']);
                $this->_registry->register('product', $productModel);
                $this->_registry->register('current_product', $productModel);
                $blockInstance1 = $resultPage->getLayout()->getBlock('product.info.form.options');
                $responseData['detail_html'] = $blockInstance->getProductDetailsHtml($productModel);
                $responseData['options_html'] = $blockInstance1->getChildHtml();
                $this->_registry->unregister('product');
                $this->_registry->unregister('current_product');
            }
            /** @var \Magento\Framework\Controller\Result\Json $resultJson */
            $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
            $resultJson->setData($responseData);
            return $resultJson;
        } catch (\Exception $e) {
            $responseData = [
                'error' => $e->getMessage(),
                'errorcode' => $e->getCode(),
            ];
            /** @var \Magento\Framework\Controller\Result\Json $resultJson */
            $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
            $resultJson->setData($responseData);
            return $resultJson;
        }
    }
}
