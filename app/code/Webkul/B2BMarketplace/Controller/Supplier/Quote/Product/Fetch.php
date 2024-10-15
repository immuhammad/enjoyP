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

namespace Webkul\B2BMarketplace\Controller\Supplier\Quote\Product;

use Magento\Framework\App\Action\Action;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;

class Fetch extends \Magento\Customer\Controller\AbstractAccount
{
    /**
     * @var FormKeyValidator
     */
    private $_formKeyValidator;

    /**
     * @param Context $context
     * @param Session $customerSession
     * @param \Webkul\B2BMarketplace\Model\ItemFactory $item
     * @param \Webkul\B2BMarketplace\Helper\Quote $quoteHelper
     * @param FormKeyValidator $formKeyValidator
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        \Webkul\B2BMarketplace\Model\ItemFactory $item,
        \Webkul\B2BMarketplace\Helper\Quote $quoteHelper,
        FormKeyValidator $formKeyValidator,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        parent::__construct($context);
        $this->_customerSession = $customerSession;
        $this->_item = $item;
        $this->_quoteHelper = $quoteHelper;
        $this->_formKeyValidator = $formKeyValidator;
        $this->_resultJsonFactory = $resultJsonFactory;
    }

    /**
     * Execute
     *
     * @return void
     */
    public function execute()
    {
        $helper = $this->_objectManager->create(\Webkul\Marketplace\Helper\Data::class);
        $isPartner = $helper->isSeller();
        if ($isPartner == 1) {
            try {
                if (!$this->_formKeyValidator->validate($this->getRequest())) {
                    $result["error"] = true;
                    $result["reload"] = true;
                    $result["msg"] = __("Invalid request.");

                    $resultJson = $this->_resultJsonFactory->create();
            
                    return $resultJson->setData($result);
                }
                $ref = (int) $this->getRequest()->getParam("ref");
                $status = (int) $this->getRequest()->getParam("status");
                $collection = $this->_item->create()->getCollection();
                $collection->addFieldToFilter("main_table.quote_id", $ref);
                $sellerId = $this->_quoteHelper->getSupplierId();
                $collection->getAllSellerQuoteItemsBySellerId($sellerId);
                if ($status != -1 && $status != 0) {
                    $collection->addFieldToFilter("sent_quotes.status", $status);
                }
                $result = [];
                $result["error"] = false;
                $result["reload"] = false;
                $result["items"] = [];
                foreach ($collection as $item) {
                    $items["product_name"] = $item['name'];
                    $items["product_url"] = "";
                    $items["qty"] = $item['qty'];
                    $items["status"] = $this->_quoteHelper->getStatusLabel($item['status']);
                    $items["css_class"] = $this->_quoteHelper->getStatusLabelClass($item['status']);
                    if ($item['buying_lead_status']) {
                        $collectionLeads = $this->_item->create()->getCollection()
                            ->addFieldToFilter("main_table.entity_id", $item["entity_id"])
                            ->getCollectionBySupplier();
                    }
                    $result["items"][] = $items;
                }
            } catch (\Exception $e) {
                $result["error"] = true;
                $result["reload"] = true;
                $result["msg"] = $e->getMessage();
                $result["items"] = [];
            }

            $resultJson = $this->_resultJsonFactory->create();
            return $resultJson->setData($result);
        } else {
            return $this->resultRedirectFactory->create()->setPath(
                'marketplace/account/becomeseller',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }
}
