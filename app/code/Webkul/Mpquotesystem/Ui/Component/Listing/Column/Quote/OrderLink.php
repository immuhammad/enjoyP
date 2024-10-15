<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_Mpquotesystem
 * @author    Webkul
 * @copyright Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Mpquotesystem\Ui\Component\Listing\Column\Quote;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class to get Order Link.
 */
class OrderLink extends Column
{
    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * Constructor.
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param \Webkul\Mpquotesystem\Ui\Component\Listing\Column\Quote\Status\QuoteOptions $quoteOptions
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        \Webkul\Mpquotesystem\Ui\Component\Listing\Column\Quote\Status\QuoteOptions $quoteOptions,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->quoteOptions = $quoteOptions;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source.
     *
     * @param array $dataSource
     *
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            $statuses = $this->quoteOptions->getOptionArray();
            foreach ($dataSource['data']['items'] as &$item) {
                if (isset($item['order_id']) && $item['order_id']) {
                    $item[$fieldName] = "<a href='".$this->urlBuilder->getUrl(
                        'sales/order/view',
                        ['order_id' => $item['order_id']]
                    )."' target='blank' title='".__('View Order')."'>".$statuses[$item[$fieldName]].'</a>';
                } else {
                    $item[$fieldName] = $statuses[$item[$fieldName]];
                }
            }
        }

        return $dataSource;
    }
}
