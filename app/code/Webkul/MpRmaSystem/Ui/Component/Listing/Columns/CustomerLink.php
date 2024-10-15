<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpRmaSystem
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpRmaSystem\Ui\Component\Listing\Columns;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class Customerlink Column
 */
class CustomerLink extends Column
{
    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * Initialize Dependencies
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        parent::__construct(
            $context,
            $uiComponentFactory,
            $components,
            $data
        );
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item['id'])) {
                    $url = $this->urlBuilder->getUrl('customer/index/edit', ['id' => $item['customer_id']]);
                    $customerId = (int) $item['customer_id'];
                    if ($customerId > 0) {
                        $html = "<a href='".$url."' target='_blank' title='".__('View Customer')."'>";
                        $html .= $item[$fieldName];
                        $html .= "</a>";
                        $item[$fieldName] = $html;
                    } else {
                        $item[$fieldName] = "Guest";
                    }
                }
            }
        }

        return $dataSource;
    }
}
