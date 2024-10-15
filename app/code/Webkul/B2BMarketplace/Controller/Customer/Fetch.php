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

namespace Webkul\B2BMarketplace\Controller\Customer;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;

class Fetch extends Action
{
    /**
     * @var FormKeyValidator
     */
    private $_formKeyValidator;

    /**
     * @param Context $context
     * @param \Webkul\B2BMarketplace\Model\SupplierFactory $supplierFactory
     * @param \Webkul\B2BMarketplace\Helper\Data $helper
     * @param FormKeyValidator $formKeyValidator
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     */
    public function __construct(
        Context $context,
        \Webkul\B2BMarketplace\Model\SupplierFactory $supplierFactory,
        \Webkul\B2BMarketplace\Helper\Data $helper,
        FormKeyValidator $formKeyValidator,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        $this->_supplierFactory = $supplierFactory;
        $this->_helper = $helper;
        $this->_formKeyValidator = $formKeyValidator;
        $this->_resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        try {
            if (!$this->_formKeyValidator->validate($this->getRequest())) {
                $result["error"] = true;
                $result["reload"] = true;
                $result["msg"] = __("Invalid request.");

                $resultJson = $this->_resultJsonFactory->create();
        
                return $resultJson->setData($result);
            }
            $result = [];
            $result["error"] = false;
            $result["msg"] = "";
            $result["reload"] = false;
            if ($this->getRequest()->isPost()) {
                if ($this->_helper->isUserLoggedIn()) {
                    $result = $this->_helper->fetchSupplierMessages($this->getRequest()->getParam("ref"));
                    $result["error"] = false;
                    $result["msg"] = "success";
                    $result["reload"] = false;
                } else {
                    $result["error"] = true;
                    $result["msg"] = __("Logged Out.");
                    $result["reload"] = true;
                }
            } else {
                $result["error"] = true;
                $result["msg"] = __("Invalid request.");
                $result["reload"] = true;
            }
        } catch (\Exception $e) {
            $result["error"] = true;
            $result["reload"] = true;
            $result["msg"] = $e->getMessage();
        }

        $resultJson = $this->_resultJsonFactory->create();
        return $resultJson->setData($result);
    }
}
