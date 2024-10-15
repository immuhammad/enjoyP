<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_Mpquotesystem
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Mpquotesystem\Controller\Rewrite\Product;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\Session;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Webkul\Marketplace\Helper\Data as HelperData;

class SaveProduct extends \Webkul\Marketplace\Controller\Product\Save
{
    /**
     * @var \Webkul\Mpquotesystem\Helper\Data
     */
    protected $quoteHelper;

    /**
     * @param Context $context
     * @param Session $customerSession
     * @param FormKeyValidator $formKeyValidator
     * @param \Webkul\Marketplace\Controller\Product\SaveProduct $saveProduct
     * @param \Magento\Catalog\Model\ResourceModel\Product $productResourceModel
     * @param HelperData $mpHelper
     * @param \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor
     * @param \Webkul\Mpquotesystem\Helper\Data $quoteHelper
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        FormKeyValidator $formKeyValidator,
        \Webkul\Marketplace\Controller\Product\SaveProduct $saveProduct,
        \Magento\Catalog\Model\ResourceModel\Product $productResourceModel,
        HelperData $mpHelper,
        \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor,
        \Webkul\Mpquotesystem\Helper\Data $quoteHelper
    ) {
        parent::__construct(
            $context,
            $customerSession,
            $formKeyValidator,
            $saveProduct,
            $productResourceModel,
            $mpHelper,
            $dataPersistor
        );
        $this->_quoteHelper = $quoteHelper;
    }
    /**
     * Save controller
     *
     * @return \Magento\Framework\Controller\Result\Redirect|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $params = $this->getRequest()->getParams();
        if ($this->_quoteHelper->getQuoteEnabled() && $params['type'] != 'grouped') {
            $quoteStatus = $params['product']['quote_status'];
            $minQuoteQty = 0;
            if ($quoteStatus == 1) {
                $minQuoteQty = $params['product']['min_quote_qty'];
            }
            if ($minQuoteQty == 0) {
                $postParams = $params;
                $updatedPostParams = [];
                foreach ($postParams['product'] as $key => $value) {
                    if ($key == 'min_quote_qty') {
                        $value = '0';
                    }
                    $updatedPostParams['product'][$key] = $value;
                }
                $postParams['product'] = $updatedPostParams['product'];
                $this->getRequest()->setParams($postParams);
            }
        }
        return parent::execute();
    }
}
