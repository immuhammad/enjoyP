<?php
/**
 * Check product type.
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
use Magento\Catalog\Model\ProductFactory;

class Checkproducttype extends \Magento\Framework\App\Action\Action
{

    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $_catalogProduct;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $_jsonHelper;

    /**
     * @param Context                                                   $context
     * @param ProductFactory                                            $catalogProduct
     * @param \Magento\Framework\Json\Helper\Data                       $jsonHelper
     * @param \Magento\Framework\Controller\Result\JsonFactory          $resultJsonFactory
     */
    public function __construct(
        Context $context,
        ProductFactory $catalogProduct,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        $this->_catalogProduct = $catalogProduct;
        $this->_jsonHelper = $jsonHelper;
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }

    /**
     * Check product type action
     */
    public function execute()
    {
        $params = $this->getRequest()->getParams();
        $productId = $params['id'];
        try {
            $product = $this->_catalogProduct->create()->load($productId);

        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }
        $resultJson = $this->resultJsonFactory->create();
        $count = 0;
        if (is_array($product->getOptions())) {
            $count = count($product->getOptions());
        }
        $result = [
            "type" => $product->getTypeId(),
            "name" => $product->getName(),
            "hasCustomOption" => $count
        ];
        return $resultJson->setData($result);
    }
}
