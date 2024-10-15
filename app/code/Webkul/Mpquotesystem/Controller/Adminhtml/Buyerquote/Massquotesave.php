<?php
/**
 * Save quote at admin end.
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
use Magento\Customer\Model\Url;
use Magento\Framework\Validator;

class Massquotesave extends Action
{
    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $_catalogProduct;

    /**
     * @param Context $context
     * @param Validator\FloatUtils $floatUtils
     * @param Validator\IntUtils $intUtils
     * @param ProductFactory $catalogProduct
     * @param Url $url
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Webkul\Mpquotesystem\Controller\Adminhtml\Buyerquote\Savequote $saveQuote
     */
    public function __construct(
        Context $context,
        Validator\FloatUtils $floatUtils,
        Validator\IntUtils $intUtils,
        ProductFactory $catalogProduct,
        Url $url,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Webkul\Mpquotesystem\Controller\Adminhtml\Buyerquote\Savequote $saveQuote
    ) {
        $this->floatUtils = $floatUtils;
        $this->intUtils = $intUtils;
        $this->_catalogProduct = $catalogProduct;
        $this->saveQuote = $saveQuote;
        $this->_url = $url;
        $this->jsonHelper = $jsonHelper;
        parent::__construct($context);
    }

    /**
     * Save quote from buyer.
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $result = [];
        $resultMsg = 0;
        if (!$this->getRequest()->isPost()) {
            $redirectUrl = $this->_url->getUrl('mpquotesystem/managequotes/index/');
            $this->messageManager->addError(
                __(
                    "Sorry some error occured!!!"
                )
            );
            return;
        }
        $params = $this->getRequest()->getParams();
        if (!is_array($params)) {
            $this->messageManager->addError(
                __("Sorry!! Quote can't be saved.")
            );
            return;
        }
        $productIds = explode(",", $params['product_ids']);
        $i = 0;
        foreach ($productIds as $productId) {
            $newArr = [];
            $params ["product"] = $productId;
            $product = $this->_catalogProduct->create()->load($productId);
            if ($product->getTypeId() == 'configurable') {
                $attributeData = $this->jsonHelper->jsonDecode($params[$productId]);
                
                foreach ($attributeData as $key => $value) {
                    $newArr[$key] = $value;
                }
                $params["super_attribute"] = $newArr;

            } else {
                $params["super_attribute"] = "";
            }
            if (isset($params["customOption".$productId]) && !empty($params["customOption".$productId])) {
                $customOptionData = $this->jsonHelper->jsonDecode($params["customOption".$productId]);
                $arrTemp = [];
                foreach ($customOptionData as $key => $data) {
                    $key = $key[8];
                    $arrTemp[$key] = $data;
                }
                $params["options"] = $arrTemp;
            }
            $errors = $this->validateDataMethod($params);
            if (empty($errors)) {
                $result = $this->saveQuote->saveQuoteData($params, $i);
                if ($result) {
                    $resultMsg ++;
                }
                $i++;
            } else {
                foreach ($errors as $message) {
                    $this->messageManager->addError($message);
                }
                return;
            }
        }
        if ($resultMsg == 0) {
            $this->messageManager
                ->addSuccess(__("Your Quote has been successfully sent"));
        }
    }

    /**
     * Validates quote's data added by customer.
     *
     * @param array $params
     * @return bool
     */
    public function validateDataMethod(&$params)
    {
        $errors = [];
        $data = [];
        foreach ($params as $code => $value) {
            switch ($code) {
                case 'quote_qty':
                    if (!$this->intUtils->isValid($value)) {
                        $errors[] = __('Quote Quantity can contain only integer value');
                    } else {
                        $value = preg_replace("/<script.*?\/script>/s", "", $value) ? : $value;
                        $params[$code] = $value;
                    }
                    break;
                case 'quote_price':
                    if (!$this->floatUtils->isValid($value)) {
                        $errors[] = __('Quote Price can contain only decimal or integer value');
                    } else {
                        $value = preg_replace("/<script.*?\/script>/s", "", $value) ? : $value;
                        $params[$code] = $value;
                    }
                    break;
                case 'quote_description':
                    if (trim($value) == '') {
                        $errors[] = __('Please enter the quote description');
                    } else {
                        $value = preg_replace("/<script.*?\/script>/s", "", $value) ? : $value;
                        $params[$code] = $value;
                    }
                    break;
            }
        }

        return $errors;
    }
}
