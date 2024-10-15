<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpAdvancedBookingSystem
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpAdvancedBookingSystem\Ui\Component\Listing\Columns;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Show price in column
 */
class Price extends Column
{
    /**
     * @var PriceCurrencyInterface
     */
    protected $priceFormatter;

    /**
     * Constructor
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param PriceCurrencyInterface $priceFormatter
     * @param StoreManagerInterface $storeManager
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        PriceCurrencyInterface $priceFormatter,
        StoreManagerInterface $storeManager,
        array $components = [],
        array $data = []
    ) {
        $this->priceFormatter = $priceFormatter;
        $this->storeManager = $storeManager;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        $currencyCode = $this->storeManager->getStore()->getCurrentCurrency();
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $currencyCode =  $currencyCode ? $currencyCode : null;
                $item['cancellation_charge'] = $this->priceFormatter->format(
                    $item['cancellation_charge'],
                    false,
                    PriceCurrencyInterface::DEFAULT_PRECISION,
                    null,
                    $currencyCode
                );
            }
        }
        return $dataSource;
    }
}
