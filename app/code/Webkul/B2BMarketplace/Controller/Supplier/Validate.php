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

namespace Webkul\B2BMarketplace\Controller\Supplier;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;

class Validate extends Action
{
    /**
     * @param Context $context
     * @param \Webkul\B2BMarketplace\Model\SupplierFactory $supplierFactory
     * @param \Webkul\B2BMarketplace\Helper\Data $helper
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     */
    public function __construct(
        Context $context,
        \Webkul\B2BMarketplace\Model\SupplierFactory $supplierFactory,
        \Webkul\B2BMarketplace\Helper\Data $helper,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        $this->_supplierFactory = $supplierFactory;
        $this->_helper = $helper;
        $this->_resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $result = [];
        $result["error"] = false;
        $result["reload"] = false;
        $result["msg"] = "";
        if ($this->getRequest()->isPost()) {
            if ($this->_helper->isUserLoggedIn()) {
                $result["error"] = true;
                $result["reload"] = true;
                $result["msg"] = __("Already logged in.");
            } else {
                $email = trim($this->getRequest()->getParam("email"));
                if ($this->_helper->isUserExist($email)) {
                    $result["error"] = true;
                    $result["reload"] = true;
                    $result["msg"] = __("Email id is already exist.");
                } else {
                    $result["msg"] = __("Available");
                }
            }
        } else {
            $result["error"] = true;
            $result["reload"] = true;
            $result["msg"] = __("Invalid request.");
        }

        $resultJson = $this->_resultJsonFactory->create();
        return $resultJson->setData($result);
    }
}
