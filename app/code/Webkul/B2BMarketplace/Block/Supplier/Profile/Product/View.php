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

namespace Webkul\B2BMarketplace\Block\Supplier\Profile\Product;

class View extends \Magento\Catalog\Block\Product\View
{
    /**
     * @var \Webkul\B2BMarketplace\Helper\MpHelper
     */
    protected $mpHelper;

    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Url\EncoderInterface $urlEncoderInterface,
        \Magento\Framework\Json\EncoderInterface $jsonEncoderInterface,
        \Magento\Framework\Stdlib\StringUtils $string,
        \Magento\Catalog\Helper\Product $productHelperData,
        \Magento\Catalog\Model\ProductTypes\ConfigInterface $productTypeConfig,
        \Magento\Framework\Locale\FormatInterface $localeFormat,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Webkul\B2BMarketplace\Helper\MpHelper $mpHelper,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        array $data = []
    ) {
        $this->mpHelper = $mpHelper;
        parent::__construct(
            $context,
            $urlEncoderInterface,
            $jsonEncoderInterface,
            $string,
            $productHelperData,
            $productTypeConfig,
            $localeFormat,
            $customerSession,
            $productRepository,
            $priceCurrency,
            $data
        );
    }

    protected function _prepareLayout()
    {
        if ($this->mpHelper->isMagentoSwatchesModuleInstalled()) {
            $childBlock = $this->getLayout()->createBlock(
                \Magento\Swatches\Block\Product\Renderer\Listing\Configurable::class
            );
            $this->setChild('product.info.options.wrapper', $childBlock);
        } else {
            $childBlock = $this->getLayout()->createBlock(
                \Magento\ConfigurableProduct\Block\Product\View\Type\Configurable::class
            )->setData('area', 'frontend')
            ->setTemplate('Magento_ConfigurableProduct::product/view/type/options/configurable.phtml');
            $this->setChild('product.info.options.configurable', $childBlock);
        }
    }
}
