<?php
/**
 * Webkul
 *
 * @category  Webkul
 * @package   Webkul_Mpquotesystem
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Mpquotesystem\Pricing;

use Magento\Catalog\Model\Product;
use Magento\Framework\Pricing\SaleableInterface;
use Magento\Framework\Pricing\Render as PricingRender;
use Magento\Framework\View\Element\Template;

class Render extends \Magento\Catalog\Pricing\Render
{

    /**
     * @var \Magento\Framework\Registry
     */
    public $registry;

    /**
     * @var \Webkul\Mpquotesystem\Helper\Data
     */
    public $helper;

    /**
     * @var Template\Context
     */
    public $context;

    /**
     * @param Template\Context                  $context
     * @param \Magento\Framework\Registry       $registry
     * @param \Webkul\Mpquotesystem\Helper\Data $helper
     */
    public function __construct(
        Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Webkul\Mpquotesystem\Helper\Data $helper
    ) {
        $this->registry = $registry;
        $this->helper = $helper;
        $this->context = $context;
        parent::__construct($context, $registry);
    }

    /**
     * Produce and return block's html output
     *
     * @return string
     */
    public function _toHtml()
    {
        $product = $this->getProduct();
        $modStatus = $this->helper->getQuoteEnabled();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        if ($this->helper->isShowPriceAfterLoginEnabled()) {
            $helper = $objectManager->create(\Webkul\ShowPriceAfterLogin\Helper\Data::class);
        }
        if ($this->helper->isShowPriceAfterLoginEnabled() && $helper->storeAvilability()
        && $helper->isCustomerLoggedIn() || !$this->helper->isShowPriceAfterLoginEnabled() ||
        $this->helper->isShowPriceAfterLoginEnabled() && !$helper->storeAvilability()) {
            $showPrice = (int)$this->helper->getConfigShowPrice();
            $status = $product->getQuoteStatus();
            if (!($status == 1)) {
                $product = $this->helper->getProductById($product->getId());
                $status = $product->getQuoteStatus();
            }
            
            /**
             * @var PricingRender $priceRender
             */
            $priceRender = $this->getLayout()->getBlock($this->getPriceRender());
            if ($priceRender instanceof PricingRender) {
                if ($product instanceof SaleableInterface) {
                    $arguments = $this->getData();
                    $arguments['render_block'] = $this;
                    $html = $priceRender->render($this->getPriceTypeCode(), $product, $arguments);
                    if ($modStatus && ($status == 1) && !$showPrice) {
                        if (strlen($html) > 1) {
                            return $this->helper->removePriceInfo($html);
                        }
                    }
                    return $html;
                }
            }
            return parent::_toHtml();
        }
    }
}
