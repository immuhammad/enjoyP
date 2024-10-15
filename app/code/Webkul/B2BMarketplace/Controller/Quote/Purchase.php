<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_B2BMarketplace
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\B2BMarketplace\Controller\Quote;

use Magento\Framework\App\Action\Action;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Webkul\B2BMarketplace\Model\ResourceModel\Quotation\CollectionFactory;

class Purchase extends \Magento\Customer\Controller\AbstractAccount
{
    /**
     * @var FormKeyValidator
     */
    protected $_formKeyValidator;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @param Context $context
     * @param Session $customerSession
     * @param FormKeyValidator $formKeyValidator
     * @param CollectionFactory $collectionFactory
     * @param \Webkul\B2BMarketplace\Model\ItemFactory $item
     * @param \Webkul\B2BMarketplace\Helper\Quote $quoteHelper
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        FormKeyValidator $formKeyValidator,
        CollectionFactory $collectionFactory,
        \Webkul\B2BMarketplace\Model\ItemFactory $item,
        \Webkul\B2BMarketplace\Helper\Quote $quoteHelper,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Checkout\Model\Cart $cart
    ) {
        parent::__construct($context);
        $this->_customerSession = $customerSession;
        $this->_formKeyValidator = $formKeyValidator;
        $this->collectionFactory = $collectionFactory;
        $this->_item = $item;
        $this->_quoteHelper = $quoteHelper;
        $this->_resultJsonFactory = $resultJsonFactory;
        $this->_cart = $cart;
    }

    /**
     * Execute
     *
     * @return void
     */
    public function execute()
    {
        $itemId = $this->getRequest()->getParam("item_id");
        $quoteId = $this->getRequest()->getParam("quote_id");
        $requestId = $this->getRequest()->getParam("request_id");
        if (!$this->_formKeyValidator->validate($this->getRequest())) {
            return $this->resultRedirectFactory->create()->setPath(
                'b2bmarketplace/customer/quote_request',
                [
                    'id' => $requestId,
                    '_secure' => $this->getRequest()->isSecure()
                ]
            );
        }

        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter("quote_id", $quoteId);
        $collection->addFieldToFilter("quote_item_id", $itemId);
        $collection->addFieldToFilter("request_id", $requestId);
        $collection->setOrder("entity_id", "DESC");
        $collection->getSelect()->limit(1);

        if ($collection->getSize()) {
            foreach ($collection as $item) {
                if ($item->getStatus() == \Webkul\B2BMarketplace\Helper\Quote::QUOTE_STATUS_APPROVED) {
                    $product = $this->_quoteHelper->getQuoteProduct($item);
                    try {
                        $params = [
                            'form_key' => $this->_quoteHelper->getFormKey(),
                            'product' => $product,
                            'qty' => $item->getQty(),
                            'quotation_id'=> $item->getEntityId(),
                            'price' => $product->getPrice()
                        ];
                        $request = new \Magento\Framework\DataObject();
                        $request->setData($params);
                        
                        $this->_cart->addProduct($product, $request);
                        $this->_cart->save();
                        $redirectUrl = $this->_url->getUrl('checkout/cart/index');
                        $this->messageManager->addSuccess(__("quote product successfully added to the cart"));
                    } catch (\Exception $e) {
                        $this->messageManager->addError($e->getMessage());
                    }
                }
            }
            return $this->resultRedirectFactory->create()->setPath(
                'checkout/cart/index',
                [
                    '_secure' => $this->getRequest()->isSecure()
                ]
            );
        }
        return $this->resultRedirectFactory->create()->setPath(
            'b2bmarketplace/customer/quote_request',
            [
                'id' => $requestId,
                '_secure' => $this->getRequest()->isSecure()
            ]
        );
    }
}
